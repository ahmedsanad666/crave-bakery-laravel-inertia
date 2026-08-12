<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AnalyticsService
{
    /**
     * @return array{
     *     revenue: array{value: float, change_percent: float|null, sparkline: array<int, float>},
     *     orders: array{value: int, change_percent: float|null, sparkline: array<int, int>},
     *     new_customers: array{value: int, change_percent: float|null, sparkline: array<int, int>},
     *     avg_order_value: array{value: float, change_percent: float|null, sparkline: array<int, float>}
     * }
     */
    public function overview(string $period): array
    {
        $bounds = $this->resolvePeriodBounds($period);
        $series = $this->revenueSeries($period);

        $currentRevenue = $this->sumPaidRevenue($bounds['start'], $bounds['end']);
        $previousRevenue = $this->sumPaidRevenue($bounds['previous_start'], $bounds['previous_end']);

        $currentOrders = $this->countOrders($bounds['start'], $bounds['end']);
        $previousOrders = $this->countOrders($bounds['previous_start'], $bounds['previous_end']);

        $currentCustomers = $this->countNewCustomers($bounds['start'], $bounds['end']);
        $previousCustomers = $this->countNewCustomers($bounds['previous_start'], $bounds['previous_end']);

        $currentAov = $currentOrders > 0 ? round($currentRevenue / $currentOrders, 2) : 0.0;
        $previousAov = $previousOrders > 0 ? round($previousRevenue / $previousOrders, 2) : 0.0;

        $revenueSparkline = $this->sparklineFromSeries($series, 'revenue');
        $ordersSparkline = $this->sparklineFromSeries($series, 'orders');
        $customerSparkline = $this->newCustomerSparkline($bounds['start'], $bounds['end']);
        $aovSparkline = $this->aovSparkline($series);

        return [
            'revenue' => [
                'value' => round($currentRevenue, 2),
                'change_percent' => $this->percentChange($currentRevenue, $previousRevenue),
                'sparkline' => $revenueSparkline,
            ],
            'orders' => [
                'value' => $currentOrders,
                'change_percent' => $this->percentChange($currentOrders, $previousOrders),
                'sparkline' => $ordersSparkline,
            ],
            'new_customers' => [
                'value' => $currentCustomers,
                'change_percent' => $this->percentChange($currentCustomers, $previousCustomers),
                'sparkline' => $customerSparkline,
            ],
            'avg_order_value' => [
                'value' => $currentAov,
                'change_percent' => $this->percentChange($currentAov, $previousAov),
                'sparkline' => $aovSparkline,
            ],
        ];
    }

    /**
     * @return array<int, array{label: string, revenue: float, orders: int}>
     */
    public function revenueSeries(string $period): array
    {
        $bounds = $this->resolvePeriodBounds($period);
        $config = $this->periodConfig($period);

        return match ($config['bucket']) {
            'day' => $this->dailyRevenueSeries($bounds['start'], $bounds['end']),
            'week' => $this->weeklyRevenueSeries($bounds['start'], $bounds['end']),
            'month' => $this->monthlyRevenueSeries($bounds['start'], $bounds['end']),
            default => $this->dailyRevenueSeries($bounds['start'], $bounds['end']),
        };
    }

    /**
     * @return array<int, array{id: int, name: string, count: int, percentage: float}>
     */
    public function ordersByCategory(string $period): array
    {
        $bounds = $this->resolvePeriodBounds($period);

        $rows = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('category_product', 'order_items.product_id', '=', 'category_product.product_id')
            ->join('categories', 'category_product.category_id', '=', 'categories.id')
            ->where('orders.payment_status', 'paid')
            ->where('orders.status', '!=', 'cancelled')
            ->whereBetween('orders.created_at', [$bounds['start'], $bounds['end']])
            ->groupBy('categories.id', 'categories.name')
            ->selectRaw('categories.id, categories.name, SUM(order_items.quantity) as count')
            ->orderByDesc('count')
            ->get();

        $total = (int) $rows->sum('count');

        return $rows->map(function ($row) use ($total) {
            $count = (int) $row->count;

            return [
                'id' => (int) $row->id,
                'name' => (string) $row->name,
                'count' => $count,
                'percentage' => $total > 0 ? round(($count / $total) * 100, 1) : 0.0,
            ];
        })->values()->all();
    }

    /**
     * @return array<int, array{id: int, name: string, slug: string, thumbnail: string|null, sold_count: int, revenue: float}>
     */
    public function topProducts(string $period, int $limit = 5): array
    {
        $bounds = $this->resolvePeriodBounds($period);

        $rows = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.payment_status', 'paid')
            ->where('orders.status', '!=', 'cancelled')
            ->whereBetween('orders.created_at', [$bounds['start'], $bounds['end']])
            ->whereNull('products.deleted_at')
            ->groupBy('products.id', 'products.name', 'products.slug', 'products.thumbnail')
            ->selectRaw('products.id, products.name, products.slug, products.thumbnail, SUM(order_items.quantity) as sold_count, SUM(order_items.line_total) as revenue')
            ->orderByDesc('sold_count')
            ->limit($limit)
            ->get();

        return $rows->map(fn ($row) => [
            'id' => (int) $row->id,
            'name' => (string) $row->name,
            'slug' => (string) $row->slug,
            'thumbnail' => Product::toPublicUrl($row->thumbnail),
            'sold_count' => (int) $row->sold_count,
            'revenue' => round((float) $row->revenue, 2),
        ])->values()->all();
    }

    /**
     * @return array<int, array{id: int, name: string, slug: string, stock_quantity: int, low_stock_threshold: int}>
     */
    public function lowStock(int $limit = 5): array
    {
        return Product::query()
            ->where('status', 'active')
            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->orderBy('stock_quantity')
            ->limit($limit)
            ->get(['id', 'name', 'slug', 'stock_quantity', 'low_stock_threshold'])
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'stock_quantity' => (int) $product->stock_quantity,
                'low_stock_threshold' => (int) $product->low_stock_threshold,
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{id: int, order_number: string, customer_name: string, total: float, status: string}>
     */
    public function recentOrders(int $limit = 5): array
    {
        return Order::query()
            ->notCancelled()
            ->latest()
            ->limit($limit)
            ->get(['id', 'order_number', 'first_name', 'last_name', 'total', 'status'])
            ->map(fn (Order $order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => trim($order->first_name.' '.$order->last_name),
                'total' => (float) $order->total,
                'status' => $order->status,
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{
     *     id: int,
     *     rating: int,
     *     title: string|null,
     *     body_excerpt: string,
     *     status: string,
     *     created_at: string|null,
     *     customer: array{id: int, name: string, avatar: string|null}|null,
     *     product: array{id: int, name: string}|null
     * }>
     */
    public function recentReviews(int $limit = 3): array
    {
        return Review::query()
            ->with(['user:id,name,email,avatar', 'product:id,name'])
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (Review $review) => [
                'id' => $review->id,
                'rating' => (int) $review->rating,
                'title' => $review->title,
                'body_excerpt' => Str::limit(strip_tags((string) $review->body), 120),
                'status' => $review->status,
                'created_at' => $review->created_at?->toIso8601String(),
                'customer' => $review->user ? [
                    'id' => $review->user->id,
                    'name' => $review->user->name,
                    'avatar' => AdminProfileService::avatarUrl($review->user->avatar),
                ] : null,
                'product' => $review->product ? [
                    'id' => $review->product->id,
                    'name' => $review->product->name,
                ] : null,
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{type: string, title: string, at: string|null}>
     */
    public function recentActivity(int $limit = 6): array
    {
        $orders = Order::query()
            ->latest()
            ->limit($limit)
            ->get(['id', 'order_number', 'created_at']);

        $reviews = Review::query()
            ->with('user:id,name')
            ->latest()
            ->limit($limit)
            ->get(['id', 'user_id', 'created_at']);

        $customers = User::query()
            ->customers()
            ->latest()
            ->limit($limit)
            ->get(['id', 'name', 'created_at']);

        $events = collect();

        foreach ($orders as $order) {
            $events->push([
                'type' => 'order',
                'title' => 'New order '.$order->order_number,
                'at' => $order->created_at?->toIso8601String(),
            ]);
        }

        foreach ($reviews as $review) {
            $events->push([
                'type' => 'review',
                'title' => ($review->user?->name ?? 'A customer').' left a review',
                'at' => $review->created_at?->toIso8601String(),
            ]);
        }

        foreach ($customers as $customer) {
            $events->push([
                'type' => 'user',
                'title' => $customer->name.' signed up',
                'at' => $customer->created_at?->toIso8601String(),
            ]);
        }

        return $events
            ->sortByDesc('at')
            ->take($limit)
            ->values()
            ->all();
    }

    public function activeDeliveries(): int
    {
        return Order::query()
            ->whereIn('status', ['processing', 'shipped'])
            ->count();
    }

    /**
     * @return array{
     *     start: Carbon,
     *     end: Carbon,
     *     previous_start: Carbon,
     *     previous_end: Carbon
     * }
     */
    private function resolvePeriodBounds(string $period): array
    {
        $config = $this->periodConfig($period);
        $end = now()->endOfDay();

        if (isset($config['days'])) {
            $days = $config['days'];
            $start = now()->subDays($days - 1)->startOfDay();
            $previousEnd = $start->copy()->subSecond();
            $previousStart = $previousEnd->copy()->subDays($days - 1)->startOfDay();

            return [
                'start' => $start,
                'end' => $end,
                'previous_start' => $previousStart,
                'previous_end' => $previousEnd,
            ];
        }

        $months = $config['months'];
        $start = now()->subMonths($months - 1)->startOfMonth();
        $previousEnd = $start->copy()->subSecond();
        $previousStart = $previousEnd->copy()->subMonths($months - 1)->startOfMonth();

        return [
            'start' => $start,
            'end' => $end,
            'previous_start' => $previousStart,
            'previous_end' => $previousEnd,
        ];
    }

    /**
     * @return array{days?: int, months?: int, bucket: string}
     */
    private function periodConfig(string $period): array
    {
        return match ($period) {
            '7d' => ['days' => 7, 'bucket' => 'day'],
            '1m' => ['days' => 30, 'bucket' => 'day'],
            '3m' => ['days' => 90, 'bucket' => 'week'],
            '1y' => ['months' => 12, 'bucket' => 'month'],
            default => ['days' => 30, 'bucket' => 'day'],
        };
    }

    private function sumPaidRevenue(Carbon $from, Carbon $to): float
    {
        return (float) $this->paidOrdersQuery()
            ->whereRaw('COALESCE(paid_at, created_at) BETWEEN ? AND ?', [$from, $to])
            ->sum('total');
    }

    private function countOrders(Carbon $from, Carbon $to): int
    {
        return Order::query()
            ->notCancelled()
            ->betweenDates($from, $to)
            ->count();
    }

    private function countNewCustomers(Carbon $from, Carbon $to): int
    {
        return User::query()
            ->customers()
            ->whereBetween('created_at', [$from, $to])
            ->count();
    }

    private function paidOrdersQuery()
    {
        return Order::query()->paid();
    }

    private function percentChange(float $current, float $previous): ?float
    {
        if ($previous <= 0) {
            return null;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    /**
     * @return array<int, array{label: string, revenue: float, orders: int}>
     */
    private function dailyRevenueSeries(Carbon $start, Carbon $end): array
    {
        $revenueByDay = $this->paidOrdersQuery()
            ->whereRaw('COALESCE(paid_at, created_at) BETWEEN ? AND ?', [$start, $end])
            ->selectRaw('DATE(COALESCE(paid_at, created_at)) as bucket, SUM(total) as revenue')
            ->groupBy('bucket')
            ->pluck('revenue', 'bucket');

        $ordersByDay = Order::query()
            ->notCancelled()
            ->betweenDates($start, $end)
            ->selectRaw('DATE(created_at) as bucket, COUNT(*) as orders')
            ->groupBy('bucket')
            ->pluck('orders', 'bucket');

        $series = [];
        $cursor = $start->copy()->startOfDay();

        while ($cursor <= $end) {
            $key = $cursor->toDateString();
            $series[] = [
                'label' => $cursor->format('M j'),
                'revenue' => round((float) ($revenueByDay[$key] ?? 0), 2),
                'orders' => (int) ($ordersByDay[$key] ?? 0),
            ];
            $cursor->addDay();
        }

        return $series;
    }

    /**
     * @return array<int, array{label: string, revenue: float, orders: int}>
     */
    private function weeklyRevenueSeries(Carbon $start, Carbon $end): array
    {
        $series = [];
        $cursor = $start->copy()->startOfWeek(Carbon::MONDAY);

        while ($cursor <= $end) {
            $weekStart = $cursor->copy()->startOfDay();
            $weekEnd = $cursor->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay();

            if ($weekEnd->greaterThan($end)) {
                $weekEnd = $end->copy();
            }

            $series[] = [
                'label' => $weekStart->format('M j'),
                'revenue' => round($this->sumPaidRevenue($weekStart, $weekEnd), 2),
                'orders' => $this->countOrders($weekStart, $weekEnd),
            ];

            $cursor->addWeek();
        }

        return $series;
    }

    /**
     * @return array<int, array{label: string, revenue: float, orders: int}>
     */
    private function monthlyRevenueSeries(Carbon $start, Carbon $end): array
    {
        $revenueRows = $this->paidOrdersQuery()
            ->whereRaw('COALESCE(paid_at, created_at) BETWEEN ? AND ?', [$start, $end])
            ->selectRaw('DATE_FORMAT(COALESCE(paid_at, created_at), "%Y-%m") as bucket, SUM(total) as revenue')
            ->groupBy('bucket')
            ->pluck('revenue', 'bucket');

        $orderRows = Order::query()
            ->notCancelled()
            ->betweenDates($start, $end)
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as bucket, COUNT(*) as orders')
            ->groupBy('bucket')
            ->pluck('orders', 'bucket');

        $series = [];
        $cursor = $start->copy()->startOfMonth();

        while ($cursor <= $end) {
            $key = $cursor->format('Y-m');
            $series[] = [
                'label' => $cursor->format('M Y'),
                'revenue' => round((float) ($revenueRows[$key] ?? 0), 2),
                'orders' => (int) ($orderRows[$key] ?? 0),
            ];
            $cursor->addMonth();
        }

        return $series;
    }

    /**
     * @param  array<int, array{label: string, revenue: float, orders: int}>  $series
     * @return array<int, float|int>
     */
    private function sparklineFromSeries(array $series, string $key): array
    {
        $points = collect($series)->pluck($key)->values();

        if ($points->count() <= 7) {
            return $points->all();
        }

        return $points->slice(-7)->values()->all();
    }

    /**
     * @return array<int, int>
     */
    private function newCustomerSparkline(Carbon $start, Carbon $end): array
    {
        $counts = User::query()
            ->customers()
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as bucket, COUNT(*) as count')
            ->groupBy('bucket')
            ->pluck('count', 'bucket');

        $series = [];
        $cursor = $start->copy()->startOfDay();

        while ($cursor <= $end) {
            $key = $cursor->toDateString();
            $series[] = (int) ($counts[$key] ?? 0);
            $cursor->addDay();
        }

        if (count($series) <= 7) {
            return $series;
        }

        return array_slice($series, -7);
    }

    /**
     * @param  array<int, array{label: string, revenue: float, orders: int}>  $series
     * @return array<int, float>
     */
    private function aovSparkline(array $series): array
    {
        $points = collect($series)->map(function (array $point) {
            $orders = (int) $point['orders'];

            return $orders > 0 ? round((float) $point['revenue'] / $orders, 2) : 0.0;
        })->values();

        if ($points->count() <= 7) {
            return $points->all();
        }

        return $points->slice(-7)->values()->all();
    }
}
