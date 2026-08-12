<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PromoCodeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isExpired = $this->expires_at !== null && $this->expires_at->isPast();

        $usageLabel = $this->max_uses === null
            ? (string) $this->used_count.' / ∞'
            : $this->used_count.' / '.$this->max_uses;

        return [
            'id' => $this->id,
            'code' => $this->code,
            'title' => $this->title,
            'type' => $this->type,
            'value' => (float) $this->value,
            'min_order_amount' => $this->min_order_amount !== null
                ? (float) $this->min_order_amount
                : null,
            'max_uses' => $this->max_uses,
            'used_count' => $this->used_count,
            'usage_label' => $usageLabel,
            'starts_at' => $this->starts_at?->toIso8601String(),
            'expires_at' => $this->expires_at?->toIso8601String(),
            'is_active' => (bool) $this->is_active,
            'is_expired' => $isExpired,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
