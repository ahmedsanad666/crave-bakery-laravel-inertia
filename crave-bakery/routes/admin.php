<?php

use App\Http\Controllers\Admin\CategoryController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;



Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::patch('categories/{category}/reorder', [CategoryController::class, 'reorder'])
        ->name('categories.reorder');

    Route::resource('categories', CategoryController::class)->except(['show']);
});