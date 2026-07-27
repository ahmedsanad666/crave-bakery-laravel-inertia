<?php

namespace App\Http\Controllers;

use App\Http\Requests\FilterCustomerOrdersRequest;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\AddressResource;
use App\Http\Resources\CartResource;
use App\Http\Resources\OrderResource;
use App\Http\Resources\PaymentGatewayResource;
use App\Http\Resources\ProfileResource;
use App\Mail\OrderConfirmed;
use App\Models\Order;
use App\Services\AddressService;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Services\StripePaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
        private readonly AddressService $addressService,
        private readonly StripePaymentService $stripePaymentService,
        private readonly PaymentService $paymentService,
    ) {
    }

    public function create(Request $request): Response|RedirectResponse
    {
        $user = $request->user();
        $quote = $this->orderService->quote($user, [
            'delivery_method' => $request->string('delivery_method')->toString() ?: 'standard',
            'promo_code' => $request->string('promo_code')->toString() ?: null,
        ]);

        if ($quote['cart']['item_count'] <= 0) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Your cart is empty.');
        }

        $addresses = $this->addressService->listForUser($user);
        $defaultAddress = $addresses->firstWhere('is_default', true) ?? $addresses->first();

        return Inertia::render('Checkout/Index', [
            'cart' => (new CartResource($quote['cart']))->resolve(),
            'totals' => $quote['totals'],
            'addresses' => AddressResource::collection($addresses)->resolve(),
            'default_address_id' => $defaultAddress?->id,
            'prefill' => [
                'email' => $user->email,
            ],
            'paymentMethods' => PaymentGatewayResource::collection(
                $this->paymentService->enabledGateways(),
            )->resolve(),
        ]);
    }

    public function store(StoreOrderRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        if (($validated['payment_method'] ?? null) === 'stripe') {
            $quote = $this->orderService->quote($user, [
                'delivery_method' => $validated['delivery_method'] ?? 'standard',
                'promo_code' => $validated['promo_code'] ?? null,
            ]);

            if (($quote['cart']['item_count'] ?? 0) <= 0) {
                return redirect()
                    ->route('cart.index')
                    ->with('error', 'Your cart is empty.');
            }

            $this->stripePaymentService->beginPendingCheckout(
                $user,
                $validated,
                $quote['totals'],
            );

            return redirect()
                ->route('checkout.payment')
                ->with('success', 'Please complete your payment.');
        }

        $order = $this->orderService->createFromCart($user, $validated);

        Mail::to($order->email)->send(new OrderConfirmed($order));

        return redirect()
            ->route('checkout.confirmation', $order)
            ->with('success', 'Order placed successfully.');
    }

    public function payment(Request $request): Response|RedirectResponse
    {
        $user = $request->user();
        $pending = $this->stripePaymentService->getPendingCheckout($user);

        if (! $pending) {
            return redirect()
                ->route('checkout')
                ->with('error', 'Your checkout session expired. Please try again.');
        }

        $quote = $this->orderService->quote($user, [
            'delivery_method' => $pending['validated']['delivery_method'] ?? 'standard',
            'promo_code' => $pending['validated']['promo_code'] ?? null,
        ]);

        if (($quote['cart']['item_count'] ?? 0) <= 0) {
            $this->stripePaymentService->clearPendingCheckout($user);

            return redirect()
                ->route('cart.index')
                ->with('error', 'Your cart is empty.');
        }

        return Inertia::render('Checkout/Payment', [
            'cart' => (new CartResource($quote['cart']))->resolve(),
            'totals' => $quote['totals'],
            'stripe_key' => $this->paymentService->publishableKey('stripe'),
        ]);
    }

    public function index(FilterCustomerOrdersRequest $request): Response
    {
        $filters = $request->validated();

        $orders = $this->orderService->paginateForCustomer(
            $request->user(),
            $filters,
        );

        return Inertia::render('Orders/Index', [
            'orders' => OrderResource::collection($orders),
            'filters' => [
                'status' => $filters['status'] ?? null,
                'search' => $filters['search'] ?? null,
            ],
            'user' => (new ProfileResource($request->user()))->resolve(),
        ]);
    }

    public function show(Request $request, Order $order): Response
    {
        $this->authorize('view', $order);

        $order = $this->orderService->findForCustomer($request->user(), $order);

        return Inertia::render('Orders/Show', [
            'order' => (new OrderResource($order))->resolve(),
            'user' => (new ProfileResource($request->user()))->resolve(),
        ]);
    }

    public function confirmation(Request $request, Order $order): Response
    {
        $this->authorize('view', $order);

        $order = $this->orderService->findForCustomer($request->user(), $order);

        return Inertia::render('Checkout/Confirmation', [
            'order' => (new OrderResource($order))->resolve(),
        ]);
    }
}
