<?php

namespace App\Services;

use App\Models\EmailDispatch;
use App\Models\EmailTemplate;

class EmailDispatchService
{
    public function log(
        string $slug,
        string $recipient,
        ?int $orderId = null,
        bool $isTest = false,
    ): EmailDispatch {
        return EmailDispatch::query()->create([
            'email_template_slug' => $slug,
            'recipient' => $recipient,
            'order_id' => $orderId,
            'is_test' => $isTest,
            'sent_at' => now(),
        ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, EmailDispatch>
     */
    public function recentInvoiceDispatches(int $limit = 15)
    {
        return EmailDispatch::query()
            ->where('email_template_slug', EmailTemplate::SLUG_PAYMENT_RECEIVED)
            ->where('is_test', false)
            ->with('order')
            ->orderByDesc('sent_at')
            ->limit($limit)
            ->get();
    }
}
