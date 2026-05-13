<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\Coupon;
use App\Mail\AbandonedCartMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Str;

class SendAbandonedCartReminders extends Command
{
    protected $signature = 'orders:abandoned-reminders';
    protected $description = 'Busca órdenes pendientes (carritos abandonados) de hace más de 2 horas y envía un recordatorio con descuento';

    public function handle()
    {
        $this->info('Buscando carritos abandonados...');

        // Órdenes pendientes, de hace más de 2 horas, a las que NO se les haya enviado recordatorio
        $abandonedOrders = Order::where('status', Order::STATUS_PENDING)
            ->where('created_at', '<=', Carbon::now()->subHours(2))
            ->where('created_at', '>=', Carbon::now()->subDays(2)) // Limitar a los últimos 2 días por seguridad
            ->where('abandoned_reminder_sent', false)
            ->with('items.product')
            ->get();

        if ($abandonedOrders->isEmpty()) {
            $this->info('No hay carritos abandonados pendientes de recordatorio.');
            return;
        }

        foreach ($abandonedOrders as $order) {
            try {
                // Crear un cupón de 10% automático para este usuario
                $coupon = Coupon::create([
                    'code' => 'VUELVE-' . strtoupper(Str::random(5)),
                    'discount_percentage' => 10,
                    'is_active' => true,
                    'max_uses' => 1,
                    'used_count' => 0,
                ]);

                Mail::to($order->customer_email)->send(new AbandonedCartMail($order, $coupon));

                $order->update(['abandoned_reminder_sent' => true]);

                $this->info("Recordatorio enviado a la orden #{$order->id} ({$order->customer_email})");
                Log::info("Carro Abandonado: Recordatorio enviado a orden #{$order->id}");

            } catch (\Exception $e) {
                Log::error("Error enviando recordatorio de carro abandonado (Orden #{$order->id}): " . $e->getMessage());
                $this->error("Error enviando recordatorio a la orden #{$order->id}");
            }
        }

        $this->info('Proceso finalizado.');
    }
}
