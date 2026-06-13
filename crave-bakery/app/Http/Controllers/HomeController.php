<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductResource;
use App\Models\Category;
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
            ->limit(6)
            ->get();

        $featuredCategories = Category::query()
            ->where('show_in_homepage', true)
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->limit(4)
            ->get();

        return Inertia::render('Home', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'featuredProducts' => ProductResource::collection($featuredProducts)->resolve(),
            'featuredCategories' => CategoryResource::collection($featuredCategories)->resolve(),
        ]);
    }
}
