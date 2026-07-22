<?php

namespace App\Services;

use App\Models\Review;
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
