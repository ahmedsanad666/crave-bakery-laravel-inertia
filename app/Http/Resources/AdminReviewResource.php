<?php

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminReviewResource extends JsonResource
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
            'body_excerpt' => Str::limit(strip_tags((string) $this->body), 120),
            'status' => $this->status,
            'flag_reason' => $this->flag_reason,
            'flagged_at' => $this->flagged_at?->toIso8601String(),
            'helpful_yes' => (int) $this->helpful_yes,
            'helpful_no' => (int) $this->helpful_no,
            'admin_response' => $this->admin_response,
            'admin_response_at' => $this->admin_response_at?->toIso8601String(),
            'is_verified_purchase' => (bool) $this->is_verified_purchase,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'customer' => $this->when(
                $this->relationLoaded('user') && $this->user,
                fn () => [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                    'avatar' => $this->user->avatar,
                    'phone' => $this->user->phone ?? null,
                    'reviews_count' => (int) ($this->user->reviews_count ?? 0),
                    'avg_rating' => round((float) ($this->user->reviews_avg_rating ?? 0), 1),
                ],
            ),
            'product' => $this->when(
                $this->relationLoaded('product') && $this->product,
                fn () => [
                    'id' => $this->product->id,
                    'name' => $this->product->name,
                    'slug' => $this->product->slug,
                    'thumbnail' => Product::toPublicUrl($this->product->thumbnail),
                    'price' => (float) ($this->product->sale_price ?? $this->product->regular_price ?? 0),
                ],
            ),
            'order' => $this->when(
                $this->relationLoaded('order') && $this->order,
                fn () => [
                    'id' => $this->order->id,
                    'order_number' => $this->order->order_number,
                ],
            ),
            'photos' => $this->when(
                $this->relationLoaded('reviewPhotos'),
                fn () => $this->reviewPhotos->map(fn ($photo) => [
                    'id' => $photo->id,
                    'path' => $this->photoUrl($photo->path),
                ])->values()->all(),
            ),
        ];
    }

    private function photoUrl(?string $path): ?string
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
}
