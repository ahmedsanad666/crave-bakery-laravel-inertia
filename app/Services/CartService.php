<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function resolveCart(Request $request): ?Cart
    {
        if ($request->user()) {
            // dd($request->user()->toArray());
           
            return Cart::query()
                ->where('user_id', $request->user()->id)
                ->first();
        }

        return Cart::query()
            ->where('session_id', $request->session()->getId())
            ->whereNull('user_id')
            ->first();
    }

    public function getOrCreateCart(Request $request): Cart
    {
        $cart = $this->resolveCart($request);

        if ($cart) {
            return $cart;
        }

        if ($request->user()) {
            return Cart::query()->create([
                'user_id' => $request->user()->id,
                'session_id' => null,
            ]);
        }

        return Cart::query()->create([
            'user_id' => null,
            'session_id' => $request->session()->getId(),
        ]);
    }

    public function getCount(Request $request): int
    {
        $cart = $this->resolveCart($request);

        if (! $cart) {
            return 0;
        }

        return (int) $cart->items()->sum('quantity');
    }

    /**
     * @return array{items: Collection<int, CartItem>, item_count: int, subtotal: float}
     */
    public function getCartPayload(Request $request): array
    {
        // dd($request->all());
        $cart = $this->resolveCart($request);
       

        if (! $cart) {
            return [
                'items' => collect(),
                'item_count' => 0,
                'subtotal' => 0.0,
            ];
        }

        $items = $cart->items()
            ->with('product.categories')
            ->latest('id')
            ->get();

        $itemCount = (int) $items->sum('quantity');
        $subtotal = round($items->sum(function (CartItem $item) {
            return $this->unitPrice($item) * $item->quantity;
        }), 2);

        return [
            'items' => $items,
            'item_count' => $itemCount,
            'subtotal' => $subtotal,
        ];
    }

    /**
     * @param  array<int|string, int|string>  $selectedAttributeMap  attribute_id => value_id
     */
    public function add(
        Request $request,
        Product $product,
        int $quantity,
        array $selectedAttributeMap = [],
    ): CartItem {
        $product = Product::query()
            ->with('attributeValues.attribute')
            ->whereKey($product->id)
            ->firstOrFail();

        $selectedAttributes = $this->resolveSelectedAttributes($product, $selectedAttributeMap);

        return DB::transaction(function () use ($request, $product, $quantity, $selectedAttributes) {
            $cart = $this->getOrCreateCart($request);
            $cart->load('items');

            $existing = $cart->items->first(
                fn (CartItem $item) => $item->product_id === $product->id
                    && $this->attributesFingerprint($item->selected_attributes ?? [])
                        === $this->attributesFingerprint($selectedAttributes),
            );

            $newQuantity = ($existing?->quantity ?? 0) + $quantity;
            $this->assertProductPurchasable($product, $newQuantity);

            if ($existing) {
                $existing->update(['quantity' => $newQuantity]);

                return $existing->fresh(['product']);
            }

            return $cart->items()->create([
                'product_id' => $product->id,
                'selected_attributes' => $selectedAttributes,
                'quantity' => $quantity,
            ])->load('product');
        });
    }

    public function updateQuantity(CartItem $cartItem, int $quantity): ?CartItem
    {
        if ($quantity <= 0) {
            $this->remove($cartItem);

            return null;
        }

        $cartItem->loadMissing('product');
        $product = $cartItem->product;

        if (! $product) {
            throw ValidationException::withMessages([
                'product' => 'This product is no longer available.',
            ]);
        }

        $this->assertProductPurchasable($product, $quantity);
        $cartItem->update(['quantity' => $quantity]);

        return $cartItem->fresh(['product']);
    }

    public function remove(CartItem $cartItem): void
    {
        $cartItem->delete();
    }

    public function ensureOwnsItem(Request $request, CartItem $cartItem): void
    {
        $cart = $this->resolveCart($request);

        if (! $cart || $cartItem->cart_id !== $cart->id) {
            abort(404);
        }
    }

    public function mergeGuestCartIntoUser(User $user, string $guestSessionId): void
    {
        $guestCart = Cart::query()
            ->where('session_id', $guestSessionId)
            ->whereNull('user_id')
            ->with(['items.product.attributeValues.attribute'])
            ->first();

        if (! $guestCart) {
            return;
        }

        DB::transaction(function () use ($user, $guestCart) {
            $userCart = Cart::query()->firstOrCreate(
                ['user_id' => $user->id],
                ['session_id' => null],
            );

            $userCart->load('items');

            foreach ($guestCart->items as $guestItem) {
                $product = $guestItem->product;

                if (! $product) {
                    continue;
                }

                $fingerprint = $this->attributesFingerprint($guestItem->selected_attributes ?? []);

                $existing = $userCart->items->first(
                    fn (CartItem $item) => $item->product_id === $guestItem->product_id
                        && $this->attributesFingerprint($item->selected_attributes ?? []) === $fingerprint,
                );

                if ($existing) {
                    $mergedQty = $existing->quantity + $guestItem->quantity;

                    if ($product->canBeOrdered($mergedQty)) {
                        $existing->update(['quantity' => $mergedQty]);
                    }

                    continue;
                }

                if (! $this->isProductPurchasable($product, $guestItem->quantity)) {
                    continue;
                }

                $userCart->items()->create([
                    'product_id' => $guestItem->product_id,
                    'selected_attributes' => $guestItem->selected_attributes,
                    'quantity' => $guestItem->quantity,
                ]);
            }

            $guestCart->items()->delete();
            $guestCart->delete();
        });
    }

    public function unitPrice(CartItem $item): float
    {
        $product = $item->product;

        if (! $product) {
            return 0.0;
        }

        return (float) $product->current_price;
    }

    public function lineTotal(CartItem $item): float
    {
        return round($this->unitPrice($item) * $item->quantity, 2);
    }

    /**
     * @param  array<int|string, int|string>  $selectedAttributeMap
     * @return list<array{attribute_id: int, attribute_name: string, value_id: int, value_label: string}>
     */
    private function resolveSelectedAttributes(Product $product, array $selectedAttributeMap): array
    {
        if ($selectedAttributeMap === []) {
            return [];
        }

        $productValues = $product->attributeValues->keyBy('id');
        $resolved = [];

        foreach ($selectedAttributeMap as $attributeId => $valueId) {
            $attributeId = (int) $attributeId;
            $valueId = (int) $valueId;
            $value = $productValues->get($valueId);

            if (! $value || (int) $value->attribute_id !== $attributeId) {
                throw ValidationException::withMessages([
                    'attributes' => 'One or more selected options are invalid for this product.',
                ]);
            }

            $resolved[] = [
                'attribute_id' => $attributeId,
                'attribute_name' => $value->attribute?->name ?? '',
                'value_id' => $valueId,
                'value_label' => $value->value,
            ];
        }

        return collect($resolved)
            ->sortBy('attribute_id')
            ->values()
            ->all();
    }

    /**
     * @param  list<array<string, mixed>>|null  $selectedAttributes
     */
    private function attributesFingerprint(?array $selectedAttributes): string
    {
        $normalized = collect($selectedAttributes ?? [])
            ->map(fn (array $row) => [
                'attribute_id' => (int) ($row['attribute_id'] ?? 0),
                'value_id' => (int) ($row['value_id'] ?? 0),
            ])
            ->sortBy('attribute_id')
            ->values()
            ->all();

        return json_encode($normalized);
    }

    /**
     * @return array{items: Collection<int, CartItem>, item_count: int, subtotal: float}
     */
    public function getCartPayloadForUser(User $user): array
    {
        $cart = Cart::query()
            ->where('user_id', $user->id)
            ->first();

        if (! $cart) {
            return [
                'items' => collect(),
                'item_count' => 0,
                'subtotal' => 0.0,
                'cart' => null,
            ];
        }

        $items = $cart->items()
            ->with('product.categories')
            ->latest('id')
            ->get();

        $itemCount = (int) $items->sum('quantity');
        $subtotal = round($items->sum(function (CartItem $item) {
            return $this->unitPrice($item) * $item->quantity;
        }), 2);

        return [
            'items' => $items,
            'item_count' => $itemCount,
            'subtotal' => $subtotal,
            'cart' => $cart,
        ];
    }

    public function clearCart(Cart $cart): void
    {
        $cart->items()->delete();
    }

    public function isProductPurchasable(Product $product, int $quantity): bool
    {
        if ($product->status !== 'active' || ! $product->is_active) {
            return false;
        }

        if ($product->stock_status === 'out_of_stock' && ! $product->allow_backorders) {
            return false;
        }

        return $product->canBeOrdered($quantity);
    }

    private function assertProductPurchasable(Product $product, int $quantity): void
    {
        if (! $this->isProductPurchasable($product, $quantity)) {
            if ($product->status !== 'active' || ! $product->is_active) {
                throw ValidationException::withMessages([
                    'product' => 'This product is not available.',
                ]);
            }

            throw ValidationException::withMessages([
                'quantity' => 'Not enough stock available for this product.',
            ]);
        }
    }
}
