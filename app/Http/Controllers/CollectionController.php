<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCollectionRequest;
use App\Http\Requests\UpdateCollectionRequest;
use App\Models\Collection;
use App\Models\Product;
use App\Services\CollectionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CollectionController extends Controller
{
    public function __construct(private readonly CollectionService $collectionService)
    {
    }

    public function store(StoreCollectionRequest $request): RedirectResponse
    {
        $this->collectionService->create(
            $request->user(),
            $request->validated(),
        );

        return back()->with('success', 'Collection created.');
    }

    public function update(UpdateCollectionRequest $request, Collection $collection): RedirectResponse
    {
        $this->collectionService->update($collection, $request->validated());

        return back()->with('success', 'Collection updated.');
    }

    public function destroy(Request $request, Collection $collection): RedirectResponse
    {
        $this->authorize('delete', $collection);

        $this->collectionService->delete($collection);

        return back()->with('success', 'Collection deleted.');
    }

    public function attachProduct(Request $request, Collection $collection, Product $product): RedirectResponse
    {
        $this->authorize('update', $collection);

        $this->collectionService->attachProduct($collection, $product);

        return back()->with('success', 'Product added to collection.');
    }

    public function detachProduct(Request $request, Collection $collection, Product $product): RedirectResponse
    {
        $this->authorize('update', $collection);

        $this->collectionService->detachProduct($collection, $product);

        return back()->with('success', 'Product removed from collection.');
    }
}
