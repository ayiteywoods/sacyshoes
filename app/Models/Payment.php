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
        return $this->reference;
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
