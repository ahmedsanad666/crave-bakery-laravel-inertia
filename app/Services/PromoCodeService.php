<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PromoCode;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class PromoCodeService
{
    public function findValid(string $code, float $subtotal, ?User $user = null): PromoCode
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

        if ($user && $this->userHasUsedCode($user, $code)) {
            throw ValidationException::withMessages([
                'promo_code' => 'You have already used this promo code.',
            ]);
        }

        return $promo;
    }

    public function userHasUsedCode(User $user, string $code): bool
    {
        return Order::query()
            ->where('user_id', $user->id)
            ->where('status', '!=', 'cancelled')
            ->whereNotNull('promo_code')
            ->whereRaw('LOWER(promo_code) = ?', [mb_strtolower(trim($code))])
            ->exists();
    }

    /**
     * @return array<int, string>
     */
    public function usedCodesForUser(User $user): array
    {
        return Order::query()
            ->where('user_id', $user->id)
            ->where('status', '!=', 'cancelled')
            ->whereNotNull('promo_code')
            ->pluck('promo_code')
            ->map(fn ($usedCode) => mb_strtoupper(trim((string) $usedCode)))
            ->unique()
            ->values()
            ->all();
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

    /**
     * Active, in-window promo codes for the public homepage carousel.
     *
     * @return Collection<int, PromoCode>
     */
    public function forHomepage(int $limit = 8, ?User $user = null): Collection
    {
        $now = now();

        $query = PromoCode::query()
            ->where('is_active', true)
            ->whereNotNull('title')
            ->where('title', '!=', '')
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', $now))
            ->where(function ($q) {
                $q->whereNull('max_uses')
                    ->orWhereColumn('used_count', '<', 'max_uses');
            });

        if ($user) {
            $usedCodes = $this->usedCodesForUser($user);

            if ($usedCodes !== []) {
                $query->whereNotIn('code', $usedCodes);
            }
        }

        return $query
            ->ordered()
            ->limit($limit)
            ->get();
    }

    /**
     * @return array{total: int, active: int, expired: int, inactive: int}
     */
    public function stats(): array
    {
        $now = now();

        return [
            'total' => PromoCode::query()->count(),
            'active' => PromoCode::query()
                ->where('is_active', true)
                ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', $now))
                ->count(),
            'expired' => PromoCode::query()
                ->whereNotNull('expires_at')
                ->where('expires_at', '<=', $now)
                ->count(),
            'inactive' => PromoCode::query()->where('is_active', false)->count(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): PromoCode
    {
        return PromoCode::query()->create([
            'code' => $this->normalizeCode($data['code']),
            'title' => $data['title'],
            'type' => $data['type'],
            'value' => $data['value'],
            'min_order_amount' => $data['min_order_amount'] ?? null,
            'max_uses' => $data['max_uses'] ?? null,
            'used_count' => 0,
            'starts_at' => $data['starts_at'] ?? null,
            'expires_at' => $data['expires_at'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(PromoCode $promoCode, array $data): PromoCode
    {
        $promoCode->update([
            'code' => $this->normalizeCode($data['code']),
            'title' => $data['title'],
            'type' => $data['type'],
            'value' => $data['value'],
            'min_order_amount' => $data['min_order_amount'] ?? null,
            'max_uses' => $data['max_uses'] ?? null,
            'starts_at' => $data['starts_at'] ?? null,
            'expires_at' => $data['expires_at'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? $promoCode->is_active),
        ]);

        return $promoCode->refresh();
    }

    public function toggleActive(PromoCode $promoCode): PromoCode
    {
        $promoCode->update([
            'is_active' => ! $promoCode->is_active,
        ]);

        return $promoCode->refresh();
    }

    public function delete(PromoCode $promoCode): void
    {
        $promoCode->delete();
    }

    private function normalizeCode(string $code): string
    {
        return mb_strtoupper(trim($code));
    }
}
