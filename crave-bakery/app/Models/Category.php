<?php

namespace App\Models;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'show_in_homepage',
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
            'show_in_homepage' => 'boolean',
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
}
