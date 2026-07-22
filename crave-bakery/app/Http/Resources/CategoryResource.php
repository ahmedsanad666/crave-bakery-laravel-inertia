<?php

namespace App\Http\Resources;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'image' => Category::toPublicUrl($this->image ?? $this->og_image),
            'banner_image' => Category::toPublicUrl($this->banner_image),
            'parent_id' => $this->parent_id,
            'products_count' => $this->whenCounted('products'),
            'status' => $this->status,
            'sort_order' => $this->sort_order,
            'show_in_navigation' => $this->show_in_navigation,
            'show_in_homepage' => $this->show_in_homepage,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'parent' => $this->whenLoaded('parent', fn () => $this->parent ? [
                'id' => $this->parent->id,
                'name' => $this->parent->name,
            ] : null),
        ];
    }
}
