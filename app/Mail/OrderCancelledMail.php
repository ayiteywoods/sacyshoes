<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use App\Models\Order;
use App\Services\EmailTemplateService;
use App\Support\EmailReplacements;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderCancelledMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        $templates = app(EmailTemplateService::class);

        return new Envelope(
            subject: $templates->subject(EmailTemplate::SLUG_ORDER_CANCELLED, EmailReplacements::forOrder($this->order)),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.order-cancelled',
        );
    }
}
