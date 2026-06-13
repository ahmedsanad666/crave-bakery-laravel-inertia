<?php

namespace App\Models;

use Database\Factories\ReviewFactory;
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
        'is_verified_purchase',
        'helpful_yes',
        'helpful_no',
    ];

    protected function casts(): array
    {
        return [
            'rating'               => 'integer',
            'helpful_yes'          => 'integer',
            'helpful_no'           => 'integer',
            'is_verified_purchase' => 'boolean',
            'flagged_at'           => 'datetime',
            'admin_response_at'    => 'datetime',
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
}
