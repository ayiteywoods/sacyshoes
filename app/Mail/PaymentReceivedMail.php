<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use App\Models\Order;
use App\Services\EmailTemplateService;
use App\Services\InvoiceService;
use App\Support\EmailReplacements;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        $templates = app(EmailTemplateService::class);

        return new Envelope(
            subject: $templates->subject(EmailTemplate::SLUG_PAYMENT_RECEIVED, EmailReplacements::forOrder($this->order)),
        );
    }

    public function content(): Content
    {
        $this->order->loadMissing('items');

        return new Content(
            markdown: 'emails.payment-received',
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $invoices = app(InvoiceService::class);

        return [
            Attachment::fromData(
                fn () => $invoices->pdfBinary($this->order->fresh(['items'])),
                $invoices->pdfFilename($this->order),
            )->withMime('application/pdf'),
        ];
    }
}
