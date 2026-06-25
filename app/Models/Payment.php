<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'user_id',
        'reference',
        'provider_transaction_id',
        'provider',
        'amount',
        'currency',
        'channel',
        'status',
        'metadata',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'status' => PaymentStatus::class,
            'metadata' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function paystackReference(): ?string
    {
        $displayReference = data_get($this->metadata, 'display_reference');

        if (filled($displayReference)) {
            return (string) $displayReference;
        }

        $orderNumber = $this->order?->order_number;
        $receiptNumber = $this->paystackReceiptNumber();

        if ($orderNumber && $receiptNumber) {
            return $orderNumber.'-'.$receiptNumber;
        }

        return $orderNumber ?: $this->reference;
    }

    public function paystackReceiptNumber(): ?string
    {
        $receipt = data_get($this->metadata, 'verification.receipt_number')
            ?? data_get($this->metadata, 'webhook.data.receipt_number');

        if (filled($receipt)) {
            return (string) $receipt;
        }

        return $this->paystackTransactionId();
    }

    public function paystackTransactionId(): ?string
    {
        if ($this->provider_transaction_id) {
            return (string) $this->provider_transaction_id;
        }

        $id = data_get($this->metadata, 'verification.id')
            ?? data_get($this->metadata, 'webhook.data.id');

        return $id !== null ? (string) $id : null;
    }

    public function paystackChannel(): ?string
    {
        return $this->channel
            ?? data_get($this->metadata, 'verification.channel')
            ?? data_get($this->metadata, 'verification.authorization.channel');
    }
}
