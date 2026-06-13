<?php


namespace App\Services;


use App\Models\Cart;

use Illuminate\Http\Request;

class CartService
{

    public static function resolveCart(Request $request): ?Cart
    {
        if ($request->user()) {
            return Cart::where('user_id', $request->user()->id)->first();
        } else {
            return Cart::where('session_id', $request->session()->getId())->first();
        }
    }

    public static function getCount(Request $request): int
    {
        $cart = static::resolveCart($request);
        if (!$cart) return 0;
        return $cart->items()->sum('quantity');
    }
}
