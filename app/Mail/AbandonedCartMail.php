<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AbandonedCartMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $coupon;

    public function __construct(Order $order, $coupon)
    {
        $this->order = $order;
        $this->coupon = $coupon;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '¿Olvidaste algo en Mundo Asiático? 🎁 Aquí tienes un regalo.',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.orders.abandoned',
        );
    }
}
