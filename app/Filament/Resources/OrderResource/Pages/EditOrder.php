<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        // Sin botón de eliminar en pedidos
        return [];
    }

    protected function afterSave(): void
    {
        $order = $this->record;

        // Notificación de Despachado
        if ($order->status === \App\Models\Order::STATUS_SHIPPED) {
            try {
                Mail::to($order->customer_email)->send(new \App\Mail\OrderShippedMail($order));
            } catch (\Exception $e) {
                Log::error("No se pudo enviar OrderShippedMail: " . $e->getMessage());
            }
        }

        // Notificación de Entregado
        if ($order->status === \App\Models\Order::STATUS_DELIVERED) {
            try {
                Mail::to($order->customer_email)->send(new \App\Mail\OrderDeliveredMail($order));
            } catch (\Exception $e) {
                Log::error("No se pudo enviar OrderDeliveredMail: " . $e->getMessage());
            }
        }
    }
}
