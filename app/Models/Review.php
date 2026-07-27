<?php

namespace App\Models;

use Database\Factories\ReviewFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends Model
{
    /** @use HasFactory<ReviewFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'product_id',
        'order_id',
        'rating',
        'title',
        'body',
        'status',
        'flag_reason',
        'flagged_at',
        'admin_response',
        'admin_response_at',
        'is_verified_purchase',
        'helpful_yes',
        'helpful_no',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'helpful_yes' => 'integer',
            'helpful_no' => 'integer',
            'is_verified_purchase' => 'boolean',
            'flagged_at' => 'datetime',
            'admin_response_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function reviewPhotos(): HasMany
    {
        return $this->hasMany(ReviewPhoto::class);
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (blank($search)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
                ->orWhere('body', 'like', "%{$search}%")
                ->orWhereHas('user', function (Builder $userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhereHas('product', function (Builder $productQuery) use ($search) {
                    $productQuery->where('name', 'like', "%{$search}%");
                });
        });
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        if (blank($status)) {
            return $query;
        }

        return $query->where('status', $status);
    }

    public function scopeRating(Builder $query, mixed $rating): Builder
    {
        if ($rating === null || $rating === '') {
            return $query;
        }

        return $query->where('rating', (int) $rating);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->latest();
    }
}
