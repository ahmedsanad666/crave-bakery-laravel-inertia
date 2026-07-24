<?php

use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    Route::resource('products', ProductController::class)->except(['show']);

    Route::patch('categories/{category}/reorder', [CategoryController::class, 'reorder'])
        ->name('categories.reorder');

    Route::resource('categories', CategoryController::class)->except(['show']);

    Route::patch('attributes/reorder', [AttributeController::class, 'reorder'])
        ->name('attributes.reorder');

    Route::resource('attributes', AttributeController::class)
        ->except(['show', 'create', 'edit']);

    Route::post('orders/{order}/refund', [OrderController::class, 'refund'])
        ->name('orders.refund');

    Route::get('orders/{order}/invoice', [OrderController::class, 'invoice'])
        ->name('orders.invoice');
    Route::get('orders/{order}/invoice/pdf', [OrderController::class, 'downloadInvoice'])
        ->name('orders.invoice.pdf');

    Route::resource('orders', OrderController::class)->only(['index', 'show', 'update']);

    Route::post('reviews/{review}/respond', [ReviewController::class, 'respond'])
        ->name('reviews.respond');

    Route::resource('reviews', ReviewController::class)
        ->only(['index', 'show', 'update', 'destroy']);

    Route::resource('customers', CustomerController::class)
        ->only(['index', 'show']);

    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::patch('settings', [SettingController::class, 'update'])->name('settings.update');

    Route::middleware('super-admin')->group(function () {
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::post('users/invite', [UserController::class, 'invite'])->name('users.invite');
        Route::post('users/invitations/{invitation}/resend', [UserController::class, 'resendInvitation'])
            ->name('users.invitations.resend');
        Route::delete('users/invitations/{invitation}', [UserController::class, 'revokeInvitation'])
            ->name('users.invitations.revoke');
        Route::get('users/{adminUser}/permissions', [UserController::class, 'permissions'])
            ->name('users.permissions');
        Route::patch('users/{adminUser}/permissions', [UserController::class, 'updatePermissions'])
            ->name('users.permissions.update');
        Route::patch('users/{adminUser}/deactivate', [UserController::class, 'deactivate'])
            ->name('users.deactivate');
        Route::delete('users/{adminUser}', [UserController::class, 'destroy'])
            ->name('users.destroy');
    });
});
