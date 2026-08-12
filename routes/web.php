<?php

use App\Http\Controllers\AcceptInvitationController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\FavouriteController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\TestController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/promo', [CartController::class, 'applyPromo'])->name('cart.promo.apply');
Route::delete('/cart/promo', [CartController::class, 'removePromo'])->name('cart.promo.remove');
Route::post('/cart/{product:slug}', [CartController::class, 'add'])->name('cart.add');
Route::patch('/cart/items/{cartItem}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/items/{cartItem}', [CartController::class, 'remove'])->name('cart.remove');

Route::middleware('auth')->group(function () {
    Route::get('/checkout', [OrderController::class, 'create'])->name('checkout');
    Route::get('/checkout/payment', [OrderController::class, 'payment'])
        ->name('checkout.payment');
    Route::get('/checkout/confirmation/{order}', [OrderController::class, 'confirmation'])
        ->name('checkout.confirmation');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::post('/payment/intent', [PaymentController::class, 'createIntent'])
        ->name('payment.intent');
    Route::post('/payment/confirm', [PaymentController::class, 'confirm'])
        ->name('payment.confirm');
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/addresses', [AddressController::class, 'index'])->name('addresses.index');
    Route::post('/addresses', [AddressController::class, 'store'])->name('addresses.store');
    Route::patch('/addresses/{address}/default', [AddressController::class, 'setDefault'])
        ->name('addresses.default');
    Route::put('/addresses/{address}', [AddressController::class, 'update'])->name('addresses.update');
    Route::patch('/addresses/{address}', [AddressController::class, 'update']);
    Route::delete('/addresses/{address}', [AddressController::class, 'destroy'])->name('addresses.destroy');

    Route::get('/favourites', [FavouriteController::class, 'index'])->name('favourites.index');
    Route::post('/favourites/{product:slug}', [FavouriteController::class, 'toggle'])
        ->name('favourites.toggle');
    Route::delete('/favourites', [FavouriteController::class, 'clear'])->name('favourites.clear');

    Route::post('/products/{product:slug}/reviews', [ReviewController::class, 'store'])
        ->name('reviews.store');

    Route::post('/collections', [CollectionController::class, 'store'])->name('collections.store');
    Route::patch('/collections/{collection}', [CollectionController::class, 'update'])
        ->name('collections.update');
    Route::delete('/collections/{collection}', [CollectionController::class, 'destroy'])
        ->name('collections.destroy');
    Route::post('/collections/{collection}/products/{product:slug}', [CollectionController::class, 'attachProduct'])
        ->withoutScopedBindings()
        ->name('collections.products.attach');
    Route::delete('/collections/{collection}/products/{product:slug}', [CollectionController::class, 'detachProduct'])
        ->withoutScopedBindings()
        ->name('collections.products.detach');
});

Route::middleware('guest')->group(function () {
    Route::get('/admin-invitations/{token}', [AcceptInvitationController::class, 'show'])
        ->name('admin-invitations.show');
    Route::post('/admin-invitations/{token}', [AcceptInvitationController::class, 'accept'])
        ->name('admin-invitations.accept');
});

require __DIR__.'/auth.php';

Route::get('/test', [TestController::class, 'index'])->name('test');
