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

    public function scopePublished($query)
    {
        return $query->where('status', 'active')->where('is_active', true);
    }

    public function scopePriceRange($query, mixed $min = null, mixed $max = null)
    {
        $effectivePrice = 'COALESCE(sale_price, regular_price)';

        if ($min !== null && $min !== '') {
            $query->whereRaw("{$effectivePrice} >= ?", [(float) $min]);
        }

        if ($max !== null && $max !== '') {
            $query->whereRaw("{$effectivePrice} <= ?", [(float) $max]);
        }

        return $query;
    }

    public function scopeMinRating($query, mixed $min = null)
    {
        if ($min === null || $min === '') {
            return $query;
        }

        $min = (float) $min;

        return $query->whereIn('id', function ($sub) use ($min) {
            $sub->select('product_id')
                ->from('reviews')
                ->where('status', 'approved')
                ->whereNull('deleted_at')
                ->groupBy('product_id')
                ->havingRaw('AVG(rating) >= ?', [$min]);
        });
    }

    public function scopeAvailability($query, mixed $inStock = null, mixed $outOfStock = null)
    {
        $wantInStock = filter_var($inStock, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) === true;
        $wantOutOfStock = filter_var($outOfStock, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) === true;

        if ($wantInStock === $wantOutOfStock) {
            return $query;
        }

        if ($wantInStock) {
            return $query->where('stock_status', 'in_stock');
        }

        return $query->where('stock_status', 'out_of_stock');
    }

    public function scopeSorted($query, ?string $sort = 'recommended')
    {
        $sort = $sort ?: 'recommended';
        $effectivePrice = 'COALESCE(sale_price, regular_price)';

        return match ($sort) {
            'price_asc' => $query->orderByRaw("{$effectivePrice} asc"),
            'price_desc' => $query->orderByRaw("{$effectivePrice} desc"),
            'newest' => $query->latest('published_at'),
            default => $query->orderByDesc('is_featured')->latest('published_at'),
        };
    }

    public function getIsNewAttribute(): bool
    {
        if ($this->published_at === null) {
            return false;
        }

        return $this->published_at->greaterThanOrEqualTo(now()->subDays(30));
    }
}
