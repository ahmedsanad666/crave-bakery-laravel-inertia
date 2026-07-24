<?php

namespace App\Models;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;
    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'image',
        'banner_image',
        'image_alt',
        'default_sort',
        'show_in_navigation',
        'show_in_footer',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_image',
        'canonical_url',
        'sort_order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'show_in_navigation' => 'boolean',
            'show_in_footer' => 'boolean',
            'meta_keywords' => 'array',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'category_product')->withTimestamps();
    }

    /**
     * Convert a stored path to a browser-ready public URL.
     * Absolute http(s) and root-relative paths are returned unchanged.
     */
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

    // create scopes 

    public function scopeSearch($query,?string $search){
        if(blank($search)){
            return $query;
        }

        return $query->where(function ($query) use ($search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%");
        });
    }
    public function scopeStatus($query,?string $status){
        if(blank($status)){
            return $query;
        }
        return $query->where('status', $status);
    }

    public function scopeOrdered($query){
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
