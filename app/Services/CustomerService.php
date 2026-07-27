<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CustomerService
{
    /**
     * @return array{
     *     total: int,
     *     active: int,
     *     inactive: int,
     *     new_this_month: int,
     *     with_orders: int
     * }
     */
    public function stats(): array
    {
        $base = User::query()->customers();

        return [
            'total' => (clone $base)->count(),
            'active' => (clone $base)->where('status', 'active')->count(),
            'inactive' => (clone $base)->where('status', 'inactive')->count(),
            'new_this_month' => (clone $base)
                ->where('created_at', '>=', now()->startOfMonth())
                ->count(),
            'with_orders' => (clone $base)
                ->whereHas('shopOrders')
                ->count(),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 15);

        return User::query()
            ->customers()
            ->search($filters['search'] ?? null)
            ->status($filters['status'] ?? null)
            ->withCount(['shopOrders as orders_count'])
            ->withSum(['shopOrders as total_spent'], 'total')
            ->ordered()
            ->paginate(max(1, min($perPage, 100)))
            ->withQueryString();
    }

    public function findForAdmin(User $customer): User
    {
        if (! $customer->isUser()) {
            throw new NotFoundHttpException('Customer not found.');
        }

        $customer->load([
            'addresses',
            'orders' => fn ($query) => $query
                ->withCount('orderItems')
                ->latest()
                ->limit(20),
        ]);

        $customer->loadCount([
            'shopOrders as orders_count',
            'reviews as reviews_count',
        ]);

        $customer->loadSum(['shopOrders as total_spent'], 'total');

        $avgOrderValue = $customer->shopOrders()->avg('total');
        $lastOrderAt = $customer->shopOrders()->max('created_at');

        $customer->setAttribute(
            'avg_order_value',
            $avgOrderValue !== null ? round((float) $avgOrderValue, 2) : null,
        );
        $customer->setAttribute('last_order_at', $lastOrderAt);

        return $customer;
    }
}
