<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShopReviewResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'rating' => (int) $this->rating,
            'title' => $this->title,
            'body' => $this->body,
            'is_verified_purchase' => (bool) $this->is_verified_purchase,
            'helpful_yes' => (int) $this->helpful_yes,
            'helpful_no' => (int) $this->helpful_no,
            'admin_response' => $this->admin_response,
            'admin_response_at' => $this->admin_response_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'user' => $this->when(
                $this->relationLoaded('user') && $this->user,
                fn () => [
                    'name' => $this->user->name,
                    'avatar' => $this->user->avatar,
                ],
            ),
        ];
    }
}
