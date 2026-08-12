<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Http\Resources\PromoCodeResource;
use App\Models\Product;
use App\Services\PromoCodeService;
use App\Services\SiteSettingService;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __construct(
        private readonly PromoCodeService $promoCodeService,
    ) {}

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
            'promoCodes' => PromoCodeResource::collection(
                $this->promoCodeService->forHomepage(8, auth()->user()),
            )->resolve(),
        ])->withViewData([
            'seo' => app(SiteSettingService::class)->documentSeo(),
        ]);
    }
}
