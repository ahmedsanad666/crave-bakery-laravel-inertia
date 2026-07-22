<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'short_description',
        'description',
        'regular_price',
        'sale_price',
        'cost_price',
        'sku',
        'barcode',
        'stock_quantity',
        'low_stock_threshold',
        'allow_backorders',
        'stock_status',
        'is_featured',
        'is_active',
        'thumbnail',
        'status',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_image',
        'canonical_url',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'regular_price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'stock_quantity' => 'integer',
            'low_stock_threshold' => 'integer',
            'allow_backorders' => 'boolean',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'meta_keywords' => 'array',
            'published_at' => 'datetime',
        ];
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class)->withTimestamps();
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class, 'collection_product')->withTimestamps();
    }

    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(AttributeValue::class, 'product_attribute_values')->withTimestamps();
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function favourites(): HasMany
    {
        return $this->hasMany(Favourite::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public static function toPublicUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (
            str_starts_with($path, 'http://')
            || str_starts_with($path, 'https://')
            || str_starts_with($path, '/')
        ) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }

    public function getCurrentPriceAttribute(): string
    {
        return $this->sale_price ?? $this->regular_price;
    }

    public function getIsOnSaleAttribute(): bool
    {
        return $this->sale_price !== null && $this->sale_price < $this->regular_price;
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->stock_quantity <= $this->low_stock_threshold;
    }

    public function canBeOrdered(int $quantity = 1): bool
    {
        if ($this->stock_quantity >= $quantity) {
            return true;
        }

        return $this->allow_backorders;
    }

    public function scopeSearch($query, ?string $search)
    {
        if (blank($search)) {
            return $query;
        }

        return $query->where(function ($query) use ($search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%");
        });
    }

    public function scopeStatus($query, ?string $status)
    {
        if (blank($status)) {
            return $query;
        }

        return $query->where('status', $status);
    }

    public function scopeStockStatus($query, ?string $stockStatus)
    {
        if (blank($stockStatus)) {
            return $query;
        }

        return $query->where('stock_status', $stockStatus);
    }

    public function scopeFeatured($query, mixed $featured = null)
    {
        if ($featured === null || $featured === '') {
            return $query;
        }

        $isFeatured = filter_var($featured, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        if ($isFeatured === null) {
            return $query;
        }

        return $query->where('is_featured', $isFeatured);
    }

    public function scopeInCategory($query, mixed $categoryId)
    {
        if (blank($categoryId)) {
            return $query;
        }

        return $query->whereHas(
            'categories',
            fn ($q) => $q->where('categories.id', (int) $categoryId),
        );
    }
}
