<?php

namespace App\Models;

use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'order_number',
        'status',
        'payment_status',
        'payment_method',
        'transaction_id',
        'subtotal',
        'discount_amount',
        'promo_code',
        'delivery_fee',
        'tax_amount',
        'total',
        'first_name',
        'last_name',
        'email',
        'phone',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'postal_code',
        'country',
        'delivery_method',
        'delivery_notes',
        'estimated_delivery_at',
        'delivered_at',
        'paid_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'notes' => 'array',
            'estimated_delivery_at' => 'datetime',
            'delivered_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (blank($search)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($search) {
            $q->where('order_number', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%");
        });
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        if (blank($status)) {
            return $query;
        }

        return $query->where('status', $status);
    }

    public function scopePaymentStatus(Builder $query, ?string $paymentStatus): Builder
    {
        if (blank($paymentStatus)) {
            return $query;
        }

        return $query->where('payment_status', $paymentStatus);
    }

    public function scopeDeliveryMethod(Builder $query, ?string $method): Builder
    {
        if (blank($method)) {
            return $query;
        }

        return $query->where('delivery_method', $method);
    }

    public function scopePaymentMethod(Builder $query, ?string $method): Builder
    {
        if (blank($method)) {
            return $query;
        }

        return $query->where('payment_method', $method);
    }

    public function scopeDateFrom(Builder $query, ?string $date): Builder
    {
        if (blank($date)) {
            return $query;
        }

        return $query->whereDate('created_at', '>=', $date);
    }

    public function scopeDateTo(Builder $query, ?string $date): Builder
    {
        if (blank($date)) {
            return $query;
        }

        return $query->whereDate('created_at', '<=', $date);
    }

    public function scopeAmountMin(Builder $query, mixed $amount): Builder
    {
        if ($amount === null || $amount === '') {
            return $query;
        }

        return $query->where('total', '>=', (float) $amount);
    }

    public function scopeAmountMax(Builder $query, mixed $amount): Builder
    {
        if ($amount === null || $amount === '') {
            return $query;
        }

        return $query->where('total', '<=', (float) $amount);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->latest();
    }
}
