<?php

namespace App\Providers;

use App\Models\AdminUser;
use App\Models\SiteSetting;
use App\Models\User;
use App\Policies\AdminUserPolicy;
use App\Policies\CustomerPolicy;
use App\Policies\SiteSettingPolicy;
use App\Services\SiteSettingService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        Gate::policy(User::class, CustomerPolicy::class);
        Gate::policy(AdminUser::class, AdminUserPolicy::class);
        Gate::policy(SiteSetting::class, SiteSettingPolicy::class);

        Route::bind('customer', function (string $value) {
            return User::query()
                ->customers()
                ->whereKey($value)
                ->firstOrFail();
        });

        Route::bind('adminUser', function (string $value) {
            return AdminUser::query()
                ->whereKey($value)
                ->firstOrFail();
        });

        View::composer('app', function ($view) {
            if ($view->offsetExists('seo')) {
                return;
            }

            $view->with('seo', app(SiteSettingService::class)->documentSeo());
        });
    }
}
