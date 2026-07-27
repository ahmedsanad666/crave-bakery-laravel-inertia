<?php

namespace App\Http\Middleware;

use App\Models\Category;
use App\Services\AdminProfileService;
use App\Services\CartService;
use App\Services\SiteSettingService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'role' => $request->user()->role,
                    'avatar' => AdminProfileService::avatarUrl($request->user()->avatar),
                    'permissions' => $request->user()->permissions,
                ] : null,
            ],
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
            'siteSettings' => app(SiteSettingService::class)->publicPayload(),
            'cart' => [
                'count' => app(CartService::class)->getCount($request),
            ],
            'navCategories' => $this->navCategories(),
            'footerCategories' => $this->footerCategories(),
        ];
    }

    /**
     * Active categories flagged for the main navbar.
     *
     * @return list<array{id: int, name: string, slug: string}>
     */
    private function navCategories(): array
    {
        return Category::query()
            ->status('active')
            ->where('show_in_navigation', true)
            ->ordered()
            ->get(['id', 'name', 'slug'])
            ->map(fn (Category $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
            ])
            ->values()
            ->all();
    }

    /**
     * Up to 4 active categories flagged for the footer.
     *
     * @return list<array{id: int, name: string, slug: string}>
     */
    private function footerCategories(): array
    {
        return Category::query()
            ->status('active')
            ->where('show_in_footer', true)
            ->ordered()
            ->limit(4)
            ->get(['id', 'name', 'slug'])
            ->map(fn (Category $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
            ])
            ->values()
            ->all();
    }
}
