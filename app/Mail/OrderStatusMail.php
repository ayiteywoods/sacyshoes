<?php

namespace App\Mail;

use App\Enums\OrderStatus;
use App\Models\EmailTemplate;
use App\Models\Order;
use App\Services\EmailTemplateService;
use App\Support\EmailReplacements;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public OrderStatus $previousStatus,
    ) {}

    public function envelope(): Envelope
    {
        $templates = app(EmailTemplateService::class);

        return new Envelope(
            subject: $templates->subject(EmailTemplate::SLUG_ORDER_STATUS, EmailReplacements::forOrderStatus($this->order)),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.order-status',
        );
    }
}
