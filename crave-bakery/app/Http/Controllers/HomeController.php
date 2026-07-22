<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        $featuredProducts = Product::query()
            ->where('is_featured', true)
            ->where('status', 'active')
            ->where('is_active', true)
            ->with('categories')
            ->latest('published_at')
            ->limit(9)
            ->get();

        $featuredIds = $featuredProducts->pluck('id');

        $latestProducts = Product::query()
            ->where('status', 'active')
            ->where('is_active', true)
            ->with('categories')
            ->latest('published_at')
            ->limit(3)
            ->get();

        $excludeIds = $featuredIds->merge($latestProducts->pluck('id'))->unique();

        $recommendedProducts = Product::query()
            ->where('status', 'active')
            ->where('is_active', true)
            ->whereNotIn('id', $excludeIds)
            ->with('categories')
            ->latest('published_at')
            ->limit(4)
            ->get();

        if ($recommendedProducts->count() < 4) {
            $fill = Product::query()
                ->where('status', 'active')
                ->where('is_active', true)
                ->whereNotIn('id', $recommendedProducts->pluck('id'))
                ->with('categories')
                ->latest('published_at')
                ->limit(4 - $recommendedProducts->count())
                ->get();

            $recommendedProducts = $recommendedProducts->concat($fill);
        }

        return Inertia::render('Home', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'featuredProducts' => ProductResource::collection($featuredProducts)->resolve(),
            'latestProducts' => ProductResource::collection($latestProducts)->resolve(),
            'recommendedProducts' => ProductResource::collection($recommendedProducts)->resolve(),
        ]);
    }
}
