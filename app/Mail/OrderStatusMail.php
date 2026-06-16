<?php

namespace App\Mail;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public OrderStatus $previousStatus,
    ) {}

    public function envelope(): Envelope
    {
        $subject = match ($this->order->status) {
            OrderStatus::Shipped => 'Your order '.$this->order->order_number.' has shipped',
            OrderStatus::Delivered => 'Your order '.$this->order->order_number.' has been delivered',
            default => 'Order '.$this->order->order_number.' update',
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.order-status',
        );
    }
}
