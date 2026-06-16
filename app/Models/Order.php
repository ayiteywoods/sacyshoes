<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'user_id',
        'subtotal',
        'delivery_fee',
        'tax',
        'total',
        'payment_method',
        'payment_status',
        'status',
        'billing_full_name',
        'billing_phone',
        'billing_email',
        'billing_address',
        'billing_city',
        'billing_country',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
            'status' => OrderStatus::class,
            'payment_status' => PaymentStatus::class,
            'paid_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * @return array<int, array{label: string, completed: bool, date: ?\Illuminate\Support\Carbon, current: bool}>
     */
    public function trackingSteps(): array
    {
        if (in_array($this->status, [OrderStatus::Cancelled, OrderStatus::Refunded], true)) {
            return [
                [
                    'label' => 'Order Created',
                    'completed' => true,
                    'date' => $this->created_at,
                    'current' => false,
                ],
                [
                    'label' => $this->status->label(),
                    'completed' => true,
                    'date' => $this->updated_at,
                    'current' => true,
                ],
            ];
        }

        $status = $this->status;

        $steps = [
            [
                'label' => 'Order Created',
                'completed' => true,
                'date' => $this->created_at,
                'current' => $status === OrderStatus::PendingPayment,
            ],
            [
                'label' => 'Payment Received',
                'completed' => $this->payment_status === PaymentStatus::Paid,
                'date' => $this->paid_at,
                'current' => $status === OrderStatus::Paid,
            ],
            [
                'label' => 'Processing',
                'completed' => in_array($status, [OrderStatus::Processing, OrderStatus::ReadyForDelivery, OrderStatus::Shipped, OrderStatus::Delivered], true),
                'date' => null,
                'current' => in_array($status, [OrderStatus::Processing, OrderStatus::ReadyForDelivery], true),
            ],
            [
                'label' => 'Shipped',
                'completed' => in_array($status, [OrderStatus::Shipped, OrderStatus::Delivered], true),
                'date' => null,
                'current' => $status === OrderStatus::Shipped,
            ],
            [
                'label' => 'Delivered',
                'completed' => $status === OrderStatus::Delivered,
                'date' => $status === OrderStatus::Delivered ? $this->updated_at : null,
                'current' => $status === OrderStatus::Delivered,
            ],
        ];

        if (! collect($steps)->contains(fn (array $step) => $step['current'])) {
            $firstIncomplete = collect($steps)->first(fn (array $step) => ! $step['completed']);
            if ($firstIncomplete) {
                $steps = collect($steps)->map(function (array $step) use ($firstIncomplete) {
                    $step['current'] = $step['label'] === $firstIncomplete['label'];

                    return $step;
                })->all();
            }
        }

        return $steps;
    }
}
