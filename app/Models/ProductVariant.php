<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'sku',
        'size',
        'color',
        'heel_length',
        'quantity',
        'reserved_quantity',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'product_id' => 'integer',
            'quantity' => 'integer',
            'reserved_quantity' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function sellingPrice(): float
    {
        return $this->product->sellingPrice();
    }

    public function isInStock(): bool
    {
        return $this->is_active && $this->availableQuantity() > 0;
    }

    public function availableQuantity(): int
    {
        return max(0, $this->quantity - ($this->reserved_quantity ?? 0));
    }

    public function displayLabel(): string
    {
        $label = "Size {$this->size} · {$this->color}";

        if (filled($this->heel_length)) {
            $label .= " · {$this->heel_length} heel";
        }

        return $label;
    }

    /**
     * @return array{size: string, color: string, heel_length: string|null}
     */
    public function optionSnapshot(): array
    {
        return [
            'size' => $this->size,
            'color' => $this->color,
            'heel_length' => $this->heel_length,
        ];
    }
}
