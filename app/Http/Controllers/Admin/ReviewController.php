<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RespondToReviewRequest;
use App\Http\Requests\UpdateReviewStatusRequest;
use App\Http\Resources\AdminReviewResource;
use App\Models\Review;
use App\Services\ReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReviewController extends Controller
{
    public function __construct(
        private readonly ReviewService $reviewService,
    ) {
        $this->authorizeResource(Review::class, 'review');
    }

    public function index(Request $request): Response
    {
        $filters = [
            'search' => $request->string('search')->toString(),
            'status' => $request->string('status')->toString(),
            'rating' => $request->input('rating'),
            'per_page' => $request->integer('per_page', 15),
        ];

        $reviews = $this->reviewService->paginate($filters);

        return Inertia::render('Admin/Reviews/Index', [
            'reviews' => AdminReviewResource::collection($reviews),
            'stats' => $this->reviewService->stats(),
            'filters' => [
                'search' => $filters['search'],
                'status' => $filters['status'],
                'rating' => $filters['rating'],
            ],
        ]);
    }

    public function show(Review $review): Response
    {
        $review = $this->reviewService->findForAdmin($review);

        return Inertia::render('Admin/Reviews/Show', [
            'review' => (new AdminReviewResource($review))->resolve(),
        ]);
    }

    public function update(UpdateReviewStatusRequest $request, Review $review): RedirectResponse
    {
        $this->reviewService->updateStatus(
            $review,
            $request->validated('status'),
            $request->validated('flag_reason'),
        );

        return back()->with('success', 'Review status updated.');
    }

    public function destroy(Review $review): RedirectResponse
    {
        $this->reviewService->delete($review);

        return redirect()
            ->route('admin.reviews.index')
            ->with('success', 'Review deleted.');
    }

    public function respond(RespondToReviewRequest $request, Review $review): RedirectResponse
    {
        $this->authorize('respond', $review);

        $this->reviewService->respond(
            $review,
            $request->validated('admin_response'),
        );

        return back()->with('success', 'Admin response saved.');
    }
}
