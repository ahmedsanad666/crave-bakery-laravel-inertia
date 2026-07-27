<?php

namespace App\Http\Controllers;

use App\Http\Requests\FilterFavouritesRequest;
use App\Http\Resources\CollectionDetailResource;
use App\Http\Resources\CollectionResource;
use App\Http\Resources\FavouriteResource;
use App\Http\Resources\ProfileResource;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Favourite;
use App\Models\Product;
use App\Services\CollectionService;
use App\Services\FavouriteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FavouriteController extends Controller
{
    public function __construct(
        private readonly FavouriteService $favouriteService,
        private readonly CollectionService $collectionService,
    ) {
    }

    public function index(FilterFavouritesRequest $request): Response
    {
        $this->authorize('viewAny', Favourite::class);

        $filters = [
            'search' => $request->validated('search'),
            'category_id' => filled($request->validated('category_id'))
                ? (int) $request->validated('category_id')
                : null,
            'sort' => $request->validated('sort') ?? 'newest',
            'per_page' => (int) ($request->validated('per_page') ?? 12),
        ];

        $favourites = $this->favouriteService->paginateForUser(
            $request->user(),
            $filters,
        );

        $request->attributes->set(
            'favourited_product_ids',
            $favourites->getCollection()->pluck('product_id')->map(fn ($id) => (int) $id)->all(),
        );

        $collections = $this->collectionService->listForUser($request->user());

        $openCollection = null;
        $collectionId = $request->integer('collection') ?: null;

        if ($collectionId) {
            $collection = Collection::query()
                ->whereKey($collectionId)
                ->where('user_id', $request->user()->id)
                ->firstOrFail();

            $this->authorize('view', $collection);

            $openCollection = $this->collectionService->findForUser(
                $request->user(),
                $collection,
            );
        }

        return Inertia::render('Favourites/Index', [
            'favourites' => FavouriteResource::collection($favourites),
            'collections' => CollectionResource::collection($collections)->resolve(),
            'openCollection' => $openCollection
                ? (new CollectionDetailResource($openCollection))->resolve()
                : null,
            'filters' => $filters,
            'categoryOptions' => Category::query()
                ->status('active')
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Category $category) => [
                    'id' => $category->id,
                    'name' => $category->name,
                ])
                ->values()
                ->all(),
            'user' => (new ProfileResource($request->user()))->resolve(),
        ]);
    }

    public function toggle(Request $request, Product $product): RedirectResponse
    {
        $this->authorize('create', Favourite::class);

        $result = $this->favouriteService->toggle($request->user(), $product);

        $message = $result['favourited']
            ? 'Added to favourites.'
            : 'Removed from favourites.';

        return back()->with('success', $message)->with('favourited', $result['favourited']);
    }

    public function clear(Request $request): RedirectResponse
    {
        $this->authorize('clear', Favourite::class);

        $this->favouriteService->clear($request->user());

        return back()->with('success', 'Favourites cleared.');
    }
}
