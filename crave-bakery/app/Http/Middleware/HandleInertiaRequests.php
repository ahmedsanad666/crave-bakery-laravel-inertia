<?php

namespace App\Http\Middleware;

use App\Services\AdminProfileService;
use App\Services\CartService;
use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Services\SiteSettingService;

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
                ] : null
            ],
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
            'siteSettings' => [
                'site_name' => SiteSettingService::get('name') ?? 'Crave Bakery',
                'about' => SiteSettingService::get('about'),
                'logo' => SiteSettingService::get('logo'),
                'favicon' => SiteSettingService::get('favicon'),
                'email' => SiteSettingService::get('email'),
                'phone' => SiteSettingService::get('phone'),
                'address' => SiteSettingService::get('address'),
            ],
            'cart' => [
                'count' => CartService::getCount($request),
            ]

        ];
    }
}
