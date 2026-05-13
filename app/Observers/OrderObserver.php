<?php

namespace App\Observers;

use App\Models\Order;
use App\Mail\OrderShippedMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        // Si el estado cambió a SHIPPED y no se ha enviado el correo (o es un cambio reciente)
        if ($order->wasChanged('status') && $order->status === Order::STATUS_SHIPPED) {
            try {
                Mail::to($order->customer_email)->send(new OrderShippedMail($order));
            } catch (\Exception $e) {
                Log::error("Error enviando OrderShippedMail para la orden #{$order->id}: " . $e->getMessage());
            }
        }
    }
}
