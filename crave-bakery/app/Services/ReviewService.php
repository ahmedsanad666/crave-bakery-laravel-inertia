<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReviewService
{
    /**
     * @return array{
     *     total: int,
     *     pending: int,
     *     approved: int,
     *     flagged: int,
     *     rejected: int,
     *     avg_rating: float
     * }
     */
    public function stats(): array
    {
        return [
            'total' => Review::query()->count(),
            'pending' => Review::query()->where('status', 'pending')->count(),
            'approved' => Review::query()->where('status', 'approved')->count(),
            'flagged' => Review::query()->where('status', 'flagged')->count(),
            'rejected' => Review::query()->where('status', 'rejected')->count(),
            'avg_rating' => round(
                (float) Review::query()->where('status', 'approved')->avg('rating'),
                1,
            ),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 15);

        return Review::query()
            ->with([
                'user' => fn ($query) => $query
                    ->select('id', 'name', 'email', 'avatar', 'phone')
                    ->withCount('reviews')
                    ->withAvg('reviews as reviews_avg_rating', 'rating'),
                'product:id,name,slug,thumbnail,regular_price,sale_price',
                'order:id,order_number',
                'reviewPhotos',
            ])
            ->search($filters['search'] ?? null)
            ->status($filters['status'] ?? null)
            ->rating($filters['rating'] ?? null)
            ->ordered()
            ->paginate(max(1, min($perPage, 100)))
            ->withQueryString();
    }

    public function findForAdmin(Review $review): Review
    {
        $review->load([
            'user' => fn ($query) => $query
                ->select('id', 'name', 'email', 'avatar', 'phone')
                ->withCount('reviews')
                ->withAvg('reviews as reviews_avg_rating', 'rating'),
            'product:id,name,slug,thumbnail,regular_price,sale_price',
            'order:id,order_number',
            'reviewPhotos',
        ]);

        return $review;
    }

    public function userHasPurchased(User $user, Product $product): bool
    {
        return Order::query()
            ->where('user_id', $user->id)
            ->where('status', 'delivered')
            ->whereHas(
                'orderItems',
                fn ($query) => $query->where('product_id', $product->id),
            )
            ->exists();
    }

    public function userCanReview(User $user, Product $product): bool
    {
        if (! $this->userHasPurchased($user, $product)) {
            return false;
        }

        return ! Review::query()
            ->where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->exists();
    }

    /**
     * @param  array{rating: int, title: string, body: string}  $data
     */
    public function storeForProduct(User $user, Product $product, array $data): Review
    {
        if (! $this->userCanReview($user, $product)) {
            throw ValidationException::withMessages([
                'product' => 'You can only review products you have purchased, and only once per product.',
            ]);
        }

        $order = Order::query()
            ->where('user_id', $user->id)
            ->where('status', 'delivered')
            ->whereHas(
                'orderItems',
                fn ($query) => $query->where('product_id', $product->id),
            )
            ->latest('delivered_at')
            ->first();

        return DB::transaction(function () use ($user, $product, $data, $order) {
            return Review::query()->create([
                'user_id' => $user->id,
                'product_id' => $product->id,
                'order_id' => $order?->id,
                'rating' => (int) $data['rating'],
                'title' => $data['title'],
                'body' => $data['body'],
                'status' => 'pending',
                'is_verified_purchase' => true,
            ]);
        });
    }

    public function updateStatus(Review $review, string $status, ?string $flagReason = null): Review
    {
        if ($status === 'flagged' && blank($flagReason)) {
            throw ValidationException::withMessages([
                'flag_reason' => 'A flag reason is required when flagging a review.',
            ]);
        }

        return DB::transaction(function () use ($review, $status, $flagReason) {
            $review->status = $status;

            if ($status === 'flagged') {
                $review->flag_reason = $flagReason;
                $review->flagged_at = now();
            } else {
                $review->flag_reason = null;
                $review->flagged_at = null;
            }

            $review->save();

            return $this->findForAdmin($review->fresh());
        });
    }

    public function respond(Review $review, string $response): Review
    {
        return DB::transaction(function () use ($review, $response) {
            $review->admin_response = $response;
            $review->admin_response_at = now();
            $review->save();

            return $this->findForAdmin($review->fresh());
        });
    }

    public function delete(Review $review): void
    {
        $review->delete();
    }
}
