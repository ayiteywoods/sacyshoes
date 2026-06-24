<?php

namespace App\Services;

use App\Enums\CouponType;
use App\Models\Coupon;
use Illuminate\Validation\ValidationException;

class CouponService
{
    public const SESSION_KEY = 'checkout_coupon_id';

    public function findByCode(string $code): ?Coupon
    {
        $normalized = strtoupper(trim($code));

        if ($normalized === '') {
            return null;
        }

        return Coupon::query()
            ->whereRaw('UPPER(code) = ?', [$normalized])
            ->first();
    }

    public function validateForSubtotal(Coupon $coupon, float $subtotal): void
    {
        if (! $coupon->is_active) {
            throw ValidationException::withMessages([
                'coupon_code' => 'This coupon is no longer active.',
            ]);
        }

        if ($coupon->starts_at && now()->lt($coupon->starts_at)) {
            throw ValidationException::withMessages([
                'coupon_code' => 'This coupon is not active yet.',
            ]);
        }

        if ($coupon->expires_at && now()->gt($coupon->expires_at)) {
            throw ValidationException::withMessages([
                'coupon_code' => 'This coupon has expired.',
            ]);
        }

        if ($coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit) {
            throw ValidationException::withMessages([
                'coupon_code' => 'This coupon has reached its usage limit.',
            ]);
        }

        if ($coupon->min_order_amount !== null && $subtotal < (float) $coupon->min_order_amount) {
            throw ValidationException::withMessages([
                'coupon_code' => 'Minimum order amount for this coupon is '.config('shop.currency_symbol').' '.number_format((float) $coupon->min_order_amount, 2).'.',
            ]);
        }
    }

    public function discountAmount(Coupon $coupon, float $subtotal): float
    {
        $discount = match ($coupon->type) {
            CouponType::Percent => round($subtotal * ((float) $coupon->value / 100), 2),
            CouponType::Fixed => (float) $coupon->value,
        };

        if ($coupon->max_discount !== null) {
            $discount = min($discount, (float) $coupon->max_discount);
        }

        return round(min($discount, $subtotal), 2);
    }

    public function applyToSession(Coupon $coupon): void
    {
        session([self::SESSION_KEY => $coupon->id]);
    }

    public function clearSession(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    public function sessionCoupon(): ?Coupon
    {
        $couponId = session(self::SESSION_KEY);

        if (! $couponId) {
            return null;
        }

        return Coupon::query()->find($couponId);
    }

    public function resolveForCheckout(string $code, float $subtotal): Coupon
    {
        $coupon = $this->findByCode($code);

        if (! $coupon) {
            throw ValidationException::withMessages([
                'coupon_code' => 'Invalid coupon code.',
            ]);
        }

        $this->validateForSubtotal($coupon, $subtotal);

        return $coupon;
    }

    public function incrementUsage(Coupon $coupon): void
    {
        $coupon->increment('used_count');
    }

    public function formatSummary(Coupon $coupon): string
    {
        return match ($coupon->type) {
            CouponType::Percent => rtrim(rtrim(number_format((float) $coupon->value, 2), '0'), '.').'% off',
            CouponType::Fixed => config('shop.currency_symbol').' '.number_format((float) $coupon->value, 2).' off',
        };
    }
}
