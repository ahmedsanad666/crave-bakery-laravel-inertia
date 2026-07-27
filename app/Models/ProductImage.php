<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    protected $fillable = [
        'product_id',
        'path',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getUrlAttribute(): ?string
    {
        return Product::toPublicUrl($this->path);
    }

    public function deleteFile(): void
    {
        if (
            $this->path
            && ! str_starts_with($this->path, 'http://')
            && ! str_starts_with($this->path, 'https://')
            && ! str_starts_with($this->path, '/')
        ) {
            Storage::disk('public')->delete($this->path);
        }
    }
}
