<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ShowAnalyticsRequest;
use App\Http\Resources\AnalyticsOverviewResource;
use App\Services\AnalyticsService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private readonly AnalyticsService $analyticsService,
    ) {}

    public function index(ShowAnalyticsRequest $request): Response
    {
        $period = $request->validated('period');

        return Inertia::render('Admin/Dashboard', [
            'analytics' => (new AnalyticsOverviewResource([
                'period' => $period,
                'kpis' => $this->analyticsService->overview($period),
                'revenueSeries' => $this->analyticsService->revenueSeries($period),
                'ordersByCategory' => $this->analyticsService->ordersByCategory($period),
                'topProducts' => $this->analyticsService->topProducts($period),
                'lowStock' => $this->analyticsService->lowStock(),
                'recentOrders' => $this->analyticsService->recentOrders(),
                'recentReviews' => $this->analyticsService->recentReviews(),
                'recentActivity' => $this->analyticsService->recentActivity(),
                'activeDeliveries' => $this->analyticsService->activeDeliveries(),
            ]))->resolve(),
        ]);
    }
}
