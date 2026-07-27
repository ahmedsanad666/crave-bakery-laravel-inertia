<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttributeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'display_type' => $this->display_type,
            'sort_order' => $this->sort_order,
            'values' => $this->when(
                $this->relationLoaded('attributeValues'),
                fn () => AttributeValueResource::collection($this->attributeValues)->resolve(),
            ),
            'values_count' => (int) ($this->attribute_values_count
                ?? ($this->relationLoaded('attributeValues') ? $this->attributeValues->count() : 0)),
            'products_count' => (int) ($this->products_count ?? 0),
        ];
    }
}
