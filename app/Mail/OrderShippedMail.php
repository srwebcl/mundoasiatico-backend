<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderShippedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "🚚 Tu pedido #{$this->order->id} fue despachado — Mundo Asiático",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.orders.shipped',
            with: ['order' => $this->order],
        );
    }
}
