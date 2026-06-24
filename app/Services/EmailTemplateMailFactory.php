<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Mail\OrderCancelledMail;
use App\Mail\OrderCreatedMail;
use App\Mail\OrderStatusMail;
use App\Mail\PaymentReceivedMail;
use App\Mail\WelcomeMail;
use App\Models\EmailTemplate;
use App\Support\EmailPreviewData;
use Illuminate\Mail\Mailable;

class EmailTemplateMailFactory
{
    public function make(EmailTemplate $template): Mailable
    {
        return match ($template->slug) {
            EmailTemplate::SLUG_WELCOME => new WelcomeMail(EmailPreviewData::sampleUser()),
            EmailTemplate::SLUG_ORDER_CREATED => new OrderCreatedMail(EmailPreviewData::sampleOrder()),
            EmailTemplate::SLUG_PAYMENT_RECEIVED => new PaymentReceivedMail(EmailPreviewData::sampleOrder()),
            EmailTemplate::SLUG_ORDER_STATUS => new OrderStatusMail(
                tap(EmailPreviewData::sampleOrder(), fn ($order) => $order->status = OrderStatus::Shipped),
                OrderStatus::Processing,
            ),
            EmailTemplate::SLUG_ORDER_CANCELLED => new OrderCancelledMail(EmailPreviewData::sampleOrder()),
            default => abort(404),
        };
    }
}
