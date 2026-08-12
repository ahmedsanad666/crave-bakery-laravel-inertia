<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\ApplyCartPromoRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Http\Resources\CartResource;
use App\Http\Resources\PromoCodeResource;
use App\Models\CartItem;
use App\Models\Product;
use App\Services\CartService;
use App\Services\PromoCodeService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly PromoCodeService $promoCodeService,
    ) {
    }

    public function index(): Response
    {
        $payload = $this->cartService->getCartPayload(request());

        return Inertia::render('Cart/Index', [
            'cart' => (new CartResource($payload))->resolve(),
            'promoCodes' => PromoCodeResource::collection(
                $this->promoCodeService->forHomepage(8, request()->user()),
            )->resolve(),
        ]);
    }

    public function add(AddToCartRequest $request, Product $product): RedirectResponse
    {
        $this->cartService->add(
            $request,
            $product,
            (int) $request->validated('quantity'),
            $request->selectedAttributeMap(),
        );

        return back()->with('success', 'Added to cart.');
    }

    public function update(UpdateCartItemRequest $request, CartItem $cartItem): RedirectResponse
    {
        $this->cartService->ensureOwnsItem($request, $cartItem);

        $quantity = (int) $request->validated('quantity');
        $this->cartService->updateQuantity($cartItem, $quantity);

        $message = $quantity <= 0 ? 'Item removed from cart.' : 'Cart updated.';

        return back()->with('success', $message);
    }

    public function remove(CartItem $cartItem): RedirectResponse
    {
        $this->cartService->ensureOwnsItem(request(), $cartItem);
        $this->cartService->remove($cartItem);

        return back()->with('success', 'Item removed from cart.');
    }

    public function applyPromo(ApplyCartPromoRequest $request): RedirectResponse
    {
        $this->cartService->applyPromo(
            $request,
            $request->validated('promo_code'),
        );

        return back()->with('success', 'Promo code applied.');
    }

    public function removePromo(): RedirectResponse
    {
        $this->cartService->removePromo(request());

        return back()->with('success', 'Promo code removed.');
    }
}
