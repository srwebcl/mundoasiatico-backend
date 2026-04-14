<?php

namespace App\Observers;

use App\Mail\OrderPaidMailable;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OrderObserver
{
    /**
     * Se ejecuta ANTES de guardar cualquier cambio.
     * Guardamos el estado original para compararlo después.
     */
    public function updating(Order $order): void
    {
        // Almacenamos el estado anterior en una propiedad temporal del modelo
        $order->originalStatus = $order->getOriginal('status');
    }

    /**
     * Se ejecuta DESPUÉS de que la orden se actualiza en la DB.
     * Si el nuevo estado es "paid" y el anterior no lo era, enviamos el email.
     */
    public function updated(Order $order): void
    {
        $previousStatus = $order->originalStatus ?? null;
        $newStatus      = $order->status;

        // Solo actuar si el status cambió a "paid" desde otro estado
        if ($newStatus === Order::STATUS_PAID && $previousStatus !== Order::STATUS_PAID) {
            $this->sendOrderPaidEmail($order);
            $this->decrementStock($order);
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Lógica de Negocio Privada
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Envía el email transaccional al cliente.
     * Se ejecuta en cola si el mailer está configurado con queue.
     */
    private function sendOrderPaidEmail(Order $order): void
    {
        try {
            // Cargar relaciones necesarias para el email
            $order->loadMissing('items');

            Mail::to($order->customer_email)
                ->send(new OrderPaidMailable($order));

            Log::info("Email de confirmación enviado para orden #{$order->id} a {$order->customer_email}");

        } catch (\Exception $e) {
            // El email no interrumpe el flujo de negocio — solo logueamos el error
            Log::error("Error al enviar email orden #{$order->id}: " . $e->getMessage());
        }
    }

    /**
     * Descuenta el stock de los productos al confirmarse el pago.
     * Protegido con idempotencia: solo actúa si stock > 0.
     * Nota: el CheckoutController también descuenta stock vía Transbank webhook.
     * Este observer actúa como respaldo cuando el status se cambia manualmente
     * desde el panel de Filament.
     */
    private function decrementStock(Order $order): void
    {
        try {
            $order->loadMissing('items.product');

            foreach ($order->items as $item) {
                if ($item->product && $item->product->stock >= $item->quantity) {
                    $item->product->decrement('stock', $item->quantity);
                    Log::info("Stock decrementado: Producto #{$item->product_id} -{$item->quantity} unidades.");
                }
            }
        } catch (\Exception $e) {
            Log::error("Error al decrementar stock orden #{$order->id}: " . $e->getMessage());
        }
    }
}
