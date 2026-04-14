<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderPaidMailable extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public readonly Order $order,
    ) {}

    /**
     * El asunto del email.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "✅ Pedido #{$this->order->id} confirmado — Mundo Asiático",
        );
    }

    /**
     * La vista Blade que se renderiza como HTML del email.
     */
    public function content(): Content
    {
        return new Content(
            htmlView: 'emails.order-paid',
            with: [
                'order'        => $this->order,
                'items'        => $this->order->items,
                'shippingLabel' => match ($this->order->shipping_type) {
                    'retiro_stgo' => 'Retiro en Casa Matriz — Santiago',
                    'retiro_pm'   => 'Retiro en Sucursal — Puerto Montt',
                    'starken'     => 'Despacho a Domicilio (Starken)',
                    default       => $this->order->shipping_type,
                },
            ]
        );
    }

    /**
     * Adjuntos (sin adjuntos por ahora).
     */
    public function attachments(): array
    {
        return [];
    }
}
