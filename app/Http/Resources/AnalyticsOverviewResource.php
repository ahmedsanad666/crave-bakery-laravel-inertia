<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnalyticsOverviewResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'period' => $this->resource['period'],
            'kpis' => $this->resource['kpis'],
            'revenueSeries' => $this->resource['revenueSeries'],
            'ordersByCategory' => $this->resource['ordersByCategory'],
            'topProducts' => $this->resource['topProducts'],
            'lowStock' => $this->resource['lowStock'],
            'recentOrders' => $this->resource['recentOrders'] ?? [],
            'recentReviews' => $this->resource['recentReviews'] ?? [],
            'recentActivity' => $this->resource['recentActivity'] ?? [],
            'activeDeliveries' => (int) ($this->resource['activeDeliveries'] ?? 0),
        ];
    }
}
