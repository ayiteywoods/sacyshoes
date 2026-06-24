<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'product_variant_id',
        'product_name',
        'product_sku',
        'variant_sku',
        'variant_options',
        'quantity',
        'unit_price',
        'total_price',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'total_price' => 'decimal:2',
            'variant_options' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function optionLabel(): ?string
    {
        if ($this->variant_options) {
            $size = $this->variant_options['size'] ?? null;
            $color = $this->variant_options['color'] ?? null;
            $heel = $this->variant_options['heel_length'] ?? null;

            if ($size && $color) {
                $label = "Size {$size} · {$color}";

                if ($heel) {
                    $label .= " · {$heel} heel";
                }

                return $label;
            }
        }

        return $this->variant?->displayLabel();
    }

    public function invoiceProductTitle(): string
    {
        $color = strtolower((string) ($this->variant_options['color'] ?? ''));
        $size = (string) ($this->variant_options['size'] ?? '');

        if ($color !== '' && $size !== '') {
            return "{$this->product_name} - {$color}, {$size}";
        }

        return $this->product_name;
    }

    /**
     * @return array<int, string>
     */
    public function invoiceVariantLines(): array
    {
        $lines = [];

        if ($color = $this->variant_options['color'] ?? null) {
            $lines[] = 'color: '.strtolower((string) $color);
        }

        if ($size = $this->variant_options['size'] ?? null) {
            $lines[] = 'size: '.$size;
        }

        if ($heel = $this->variant_options['heel_length'] ?? null) {
            $lines[] = 'heel: '.strtolower((string) $heel);
        }

        return $lines;
    }
}
