<?php

namespace App\Services;

use App\Models\PromoCode;
use Illuminate\Validation\ValidationException;

class PromoCodeService
{
    public function findValid(string $code, float $subtotal): PromoCode
    {
        $promo = PromoCode::query()
            ->whereRaw('LOWER(code) = ?', [mb_strtolower(trim($code))])
            ->first();

        if (! $promo) {
            throw ValidationException::withMessages([
                'promo_code' => 'This promo code is invalid.',
            ]);
        }

        if (! $promo->is_active) {
            throw ValidationException::withMessages([
                'promo_code' => 'This promo code is no longer active.',
            ]);
        }

        if ($promo->starts_at && $promo->starts_at->isFuture()) {
            throw ValidationException::withMessages([
                'promo_code' => 'This promo code is not active yet.',
            ]);
        }

        if ($promo->expires_at && $promo->expires_at->isPast()) {
            throw ValidationException::withMessages([
                'promo_code' => 'This promo code has expired.',
            ]);
        }

        if ($promo->max_uses !== null && $promo->used_count >= $promo->max_uses) {
            throw ValidationException::withMessages([
                'promo_code' => 'This promo code has reached its usage limit.',
            ]);
        }

        if ($promo->min_order_amount !== null && $subtotal < (float) $promo->min_order_amount) {
            throw ValidationException::withMessages([
                'promo_code' => 'Your order does not meet the minimum amount for this promo code.',
            ]);
        }

        return $promo;
    }

    public function discountAmount(PromoCode $promo, float $subtotal): float
    {
        if ($subtotal <= 0) {
            return 0.0;
        }

        $discount = match ($promo->type) {
            'percentage' => $subtotal * ((float) $promo->value / 100),
            'fixed' => (float) $promo->value,
            default => 0.0,
        };

        return round(min($discount, $subtotal), 2);
    }
}
