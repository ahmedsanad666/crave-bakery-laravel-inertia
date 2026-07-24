<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Models\Product;
use App\Models\Review;
use App\Services\ReviewService;
use Illuminate\Http\RedirectResponse;

class ReviewController extends Controller
{
    public function __construct(
        private readonly ReviewService $reviewService,
    ) {
    }

    public function store(StoreReviewRequest $request, Product $product): RedirectResponse
    {
        $this->authorize('create', Review::class);

        $this->reviewService->storeForProduct(
            $request->user(),
            $product,
            $request->validated(),
        );

        return back()->with(
            'success',
            'Thank you! Your review was submitted and is pending moderation.',
        );
    }
}
