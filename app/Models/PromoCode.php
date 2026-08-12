<?php

namespace App\Models;

use Database\Factories\PromoCodeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    /** @use HasFactory<PromoCodeFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'title',
        'type',
        'value',
        'min_order_amount',
        'max_uses',
        'used_count',
        'starts_at',
        'expires_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'min_order_amount' => 'decimal:2',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function scopeSearch($query, ?string $search)
    {
        if (blank($search)) {
            return $query;
        }

        return $query->where(function ($query) use ($search) {
            $query->whereRaw('LOWER(code) like ?', ['%'.mb_strtolower($search).'%'])
                ->orWhereRaw('LOWER(title) like ?', ['%'.mb_strtolower($search).'%']);
        });
    }

    /**
     * Filter by admin status: active | inactive | expired.
     */
    public function scopeStatus($query, ?string $status)
    {
        if (blank($status)) {
            return $query;
        }

        return match ($status) {
            'active' => $query
                ->where('is_active', true)
                ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now())),
            'inactive' => $query->where('is_active', false),
            'expired' => $query
                ->whereNotNull('expires_at')
                ->where('expires_at', '<=', now()),
            default => $query,
        };
    }

    public function scopeOrdered($query)
    {
        return $query->orderByDesc('created_at')->orderBy('code');
    }
}
