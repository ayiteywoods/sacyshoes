<?php

namespace App\Models;

use App\Enums\ProductStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'sku',
        'price',
        'discount_price',
        'description',
        'quantity',
        'status',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'discount_price' => 'decimal:2',
            'status' => ProductStatus::class,
            'published_at' => 'datetime',
        ];
    }

    public function scopeVisibleOnStorefront(Builder $query): Builder
    {
        return $query
            ->where('status', ProductStatus::Active)
            ->where(function (Builder $builder) {
                $builder->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function activeVariants(): HasMany
    {
        return $this->variants()
            ->where('is_active', true)
            ->where('quantity', '>', 0);
    }

    public function primaryImage(): ?ProductImage
    {
        return $this->images()->where('is_primary', true)->first()
            ?? $this->images()->first();
    }

    public function sellingPrice(): float
    {
        return (float) ($this->discount_price ?? $this->price);
    }

    public function isActive(): bool
    {
        return $this->status === ProductStatus::Active;
    }

    public function isVisibleOnStorefront(): bool
    {
        if (! $this->isActive()) {
            return false;
        }

        return $this->published_at === null || $this->published_at->lte(now());
    }

    public function isScheduledForFuture(): bool
    {
        return $this->published_at !== null && $this->published_at->isFuture();
    }

    public function storefrontPublishLabel(): ?string
    {
        if ($this->published_at === null) {
            return null;
        }

        return $this->published_at->timezone(config('app.timezone'))->format('M j, Y g:i A');
    }

    public function isInStock(): bool
    {
        if ($this->status !== ProductStatus::Active) {
            return false;
        }

        if ($this->relationLoaded('variants')) {
            return $this->variants->contains(fn (ProductVariant $variant) => $variant->isInStock());
        }

        if ($this->variants()->exists()) {
            return $this->variants()
                ->where('is_active', true)
                ->where('quantity', '>', 0)
                ->exists();
        }

        return $this->quantity > 0;
    }

    public function totalStock(): int
    {
        if ($this->relationLoaded('variants') && $this->variants->isNotEmpty()) {
            return (int) $this->variants->where('is_active', true)->sum('quantity');
        }

        if ($this->variants()->exists()) {
            return (int) $this->variants()->where('is_active', true)->sum('quantity');
        }

        return (int) $this->quantity;
    }

    public function hasVariants(): bool
    {
        if ($this->relationLoaded('variants')) {
            return $this->variants->isNotEmpty();
        }

        return $this->variants()->exists();
    }

    public function isLowStock(): bool
    {
        $stock = $this->totalStock();

        return $stock > 0 && $stock < 10;
    }
}
