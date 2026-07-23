<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RefundOrderRequest;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Http\Resources\AdminOrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
    ) {
        $this->authorizeResource(Order::class, 'order');
    }

    public function index(Request $request): Response
    {
        $filters = [
            'search' => $request->string('search')->toString(),
            'status' => $request->string('status')->toString(),
            'payment_status' => $request->string('payment_status')->toString(),
            'payment_method' => $request->string('payment_method')->toString(),
            'delivery_method' => $request->string('delivery_method')->toString(),
            'date_from' => $request->string('date_from')->toString(),
            'date_to' => $request->string('date_to')->toString(),
            'amount_min' => $request->input('amount_min'),
            'amount_max' => $request->input('amount_max'),
            'per_page' => $request->integer('per_page', 15),
        ];

        $orders = $this->orderService->paginate($filters);

        return Inertia::render('Admin/Orders/Index', [
            'orders' => AdminOrderResource::collection($orders),
            'stats' => $this->orderService->stats(),
            'filters' => [
                'search' => $filters['search'],
                'status' => $filters['status'],
                'payment_status' => $filters['payment_status'],
                'payment_method' => $filters['payment_method'],
                'delivery_method' => $filters['delivery_method'],
                'date_from' => $filters['date_from'],
                'date_to' => $filters['date_to'],
                'amount_min' => $filters['amount_min'],
                'amount_max' => $filters['amount_max'],
            ],
        ]);
    }

    public function show(Order $order): Response
    {
        $order = $this->orderService->findForAdmin($order);

        return Inertia::render('Admin/Orders/Show', [
            'order' => (new AdminOrderResource($order))->resolve(),
        ]);
    }

    public function invoice(Order $order): Response|RedirectResponse
    {
        $this->authorize('view', $order);

        if ($order->status !== 'delivered') {
            return back()->with('error', 'Invoice is only available for completed orders.');
        }

        $order = $this->orderService->findForAdmin($order);

        return Inertia::render('Admin/Orders/Invoice', [
            'order' => (new AdminOrderResource($order))->resolve(),
        ]);
    }

    public function update(UpdateOrderStatusRequest $request, Order $order): RedirectResponse
    {
        $this->orderService->updateStatus(
            $order,
            $request->validated('status'),
            $request->validated('note'),
        );

        // notify_customer reserved for email phase
        return back()->with('success', 'Order status updated.');
    }

    public function refund(RefundOrderRequest $request, Order $order): RedirectResponse
    {
        $this->authorize('refund', $order);

        $this->orderService->refund(
            $order,
            $request->validated('reason'),
        );

        return back()->with('success', 'Order refunded successfully.');
    }
}
