<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ProductVariantResolver
{
    public function __construct(
        protected StockReservationService $reservations
    ) {}

    public function resolveForProduct(
        Product $product,
        string $size,
        string $color,
        ?string $heelLength = null
    ): ProductVariant {
        $candidates = $this->matchingVariants($product, $size, $color);

        if ($candidates->isEmpty()) {
            throw ValidationException::withMessages([
                'variant_size' => 'The selected size or color is not available.',
            ]);
        }

        if (filled($heelLength)) {
            $match = $candidates->first(
                fn (ProductVariant $variant) => $this->normalizeHeel($variant->heel_length) === $this->normalizeHeel($heelLength)
            );

            if ($match) {
                return $match;
            }

            throw ValidationException::withMessages([
                'variant_heel' => 'The selected heel length is not available.',
            ]);
        }

        if ($candidates->count() === 1) {
            return $candidates->first();
        }

        $flatHeelVariants = $candidates->filter(
            fn (ProductVariant $variant) => $this->isFlatHeel($variant->heel_length)
        );

        if ($flatHeelVariants->count() === 1) {
            return $flatHeelVariants->first();
        }

        $heelVariants = $candidates->filter(
            fn (ProductVariant $variant) => ! $this->isFlatHeel($variant->heel_length)
        );

        if ($heelVariants->count() === 1) {
            return $heelVariants->first();
        }

        throw ValidationException::withMessages([
            'variant_heel' => 'Please select a heel length for this size and color.',
        ]);
    }

    /**
     * @return Collection<int, ProductVariant>
     */
    protected function matchingVariants(Product $product, string $size, string $color): Collection
    {
        $normalizedSize = $this->normalize($size);
        $normalizedColor = $this->normalize($color);

        return $product->variants()
            ->where('is_active', true)
            ->get()
            ->filter(fn (ProductVariant $variant) => $this->reservations->availableQuantity($variant) > 0)
            ->filter(fn (ProductVariant $variant) => $this->normalize($variant->size) === $normalizedSize)
            ->filter(fn (ProductVariant $variant) => $this->normalize($variant->color) === $normalizedColor)
            ->values();
    }

    protected function normalize(?string $value): string
    {
        return strtolower(trim((string) $value));
    }

    protected function normalizeHeel(?string $value): string
    {
        return $this->normalize($value);
    }

    protected function isFlatHeel(?string $value): bool
    {
        $heel = $this->normalizeHeel($value);

        return $heel === '' || $heel === 'flat';
    }
}
