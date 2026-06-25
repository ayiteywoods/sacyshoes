<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

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
        'shipping_full_name',
        'shipping_phone',
        'shipping_email',
        'shipping_address',
        'shipping_city',
        'shipping_country',
        'shipping_region_id',
        'shipping_option_id',
        'shipping_region_name',
        'shipping_option_name',
        'customer_comment',
        'coupon_id',
        'coupon_code',
        'discount_amount',
        'shipping_fee',
        'paid_at',
        'payment_due_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'shipping_fee' => 'decimal:2',
            'tax' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'status' => OrderStatus::class,
            'payment_status' => PaymentStatus::class,
            'paid_at' => 'datetime',
            'payment_due_at' => 'datetime',
            'user_id' => 'integer',
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

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * @return array<int, array{label: string, completed: bool, date: ?Carbon, current: bool}>
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
                'current' => $status === OrderStatus::Processing,
            ],
            [
                'label' => 'Ready for Delivery',
                'completed' => in_array($status, [OrderStatus::ReadyForDelivery, OrderStatus::Shipped, OrderStatus::Delivered], true),
                'date' => null,
                'current' => $status === OrderStatus::ReadyForDelivery,
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

    public function invoiceNumber(): string
    {
        return $this->order_number;
    }

    public function invoiceDate(): Carbon
    {
        return $this->paid_at ?? $this->created_at;
    }

    public function paymentMethodLabel(): string
    {
        return (string) config('shop.payment_method_label');
    }

    public function invoiceShippingLabel(): string
    {
        if ((float) $this->shipping_fee > 0) {
            $label = config('shop.currency_symbol').number_format((float) $this->shipping_fee, 2);

            if ($this->shipping_option_name) {
                $label .= ' ('.$this->shipping_option_name.')';
            }

            return $label;
        }

        return (string) config('shop.invoice_accra_shipping_note');
    }

    public function deliveryFeeLabel(): string
    {
        $fee = (float) ($this->shipping_fee ?: $this->delivery_fee);

        if ($fee > 0) {
            $label = config('shop.currency_symbol').number_format($fee, 2);

            if ($this->shipping_option_name) {
                $label .= ' ('.$this->shipping_option_name.')';
            } elseif ($this->shipping_region_name) {
                $label .= ' ('.$this->shipping_region_name.')';
            }

            return $label;
        }

        if ($this->shipping_region_name) {
            return $this->shipping_region_name.' — pay rider on delivery';
        }

        return 'Pay rider on delivery';
    }

    public function formattedAddress(string $prefix = 'billing'): string
    {
        $lines = array_filter([
            $this->{"{$prefix}_full_name"},
            $this->{"{$prefix}_address"},
            $this->{"{$prefix}_city"},
            $this->{"{$prefix}_country"},
        ]);

        return implode(', ', $lines);
    }

    public function customerEmail(): ?string
    {
        $email = trim((string) ($this->billing_email ?: $this->shipping_email));

        return $email !== '' ? $email : null;
    }

    /**
     * @return array<int, OrderStatus>
     */
    public function adminStatusOptions(): array
    {
        if ($this->payment_status === PaymentStatus::Paid) {
            return [
                OrderStatus::Paid,
                OrderStatus::Processing,
                OrderStatus::ReadyForDelivery,
                OrderStatus::Shipped,
                OrderStatus::Delivered,
                OrderStatus::Refunded,
            ];
        }

        return [
            OrderStatus::PendingPayment,
            OrderStatus::Cancelled,
        ];
    }

    public function isTrackable(): bool
    {
        return $this->payment_status === PaymentStatus::Paid
            && ! in_array($this->status, [OrderStatus::Cancelled, OrderStatus::Refunded], true);
    }
}
