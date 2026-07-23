<?php

namespace App\Http\Controllers;

use App\Http\Requests\FilterShopProductsRequest;
use App\Http\Resources\ProductDetailResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ShopReviewResource;
use App\Models\Favourite;
use App\Models\Product;
use App\Services\ProductService;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $productService)
    {
    }

    public function index(FilterShopProductsRequest $request): Response
    {
        $result = $this->productService->paginateForShop($request->validated());

        $request->attributes->set(
            'favourited_product_ids',
            $result['favourited_product_ids'],
        );

        return Inertia::render('Products/Index', [
            'products' => ProductResource::collection($result['products']),
            'categoryOptions' => $result['categoryOptions'],
            'priceBounds' => $result['priceBounds'],
            'filters' => $result['filters'],
        ]);
    }

    public function show(Product $product): Response
    {
        $result = $this->productService->findForShop($product);

        $relatedIds = $result['relatedProducts']->pluck('id')->map(fn ($id) => (int) $id)->all();
        $favouritedRelated = [];

        if (auth()->id() && $relatedIds !== []) {
            $favouritedRelated = Favourite::query()
                ->where('user_id', auth()->id())
                ->whereIn('product_id', $relatedIds)
                ->pluck('product_id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        request()->attributes->set('favourited_product_ids', $favouritedRelated);

        return Inertia::render('Products/Show', [
            'product' => (new ProductDetailResource(
                $result['product'],
                $result['gallery'],
                $result['attributes'],
                $result['is_favourited'],
            ))->resolve(),
            'relatedProducts' => ProductResource::collection($result['relatedProducts'])->resolve(),
            'reviews' => [
                'average_rating' => $result['reviews']['average_rating'],
                'reviews_count' => $result['reviews']['reviews_count'],
                'rating_breakdown' => $result['reviews']['rating_breakdown'],
                'items' => ShopReviewResource::collection($result['reviews']['items'])->resolve(),
            ],
        ]);
    }
}
