<?php

use App\Http\Controllers\AcceptInvitationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TestController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('guest')->group(function () {
    Route::get('/admin-invitations/{token}', [AcceptInvitationController::class, 'show'])
        ->name('admin-invitations.show');
    Route::post('/admin-invitations/{token}', [AcceptInvitationController::class, 'accept'])
        ->name('admin-invitations.accept');
});

require __DIR__.'/auth.php';

Route::get('/test', [TestController::class, 'index'])->name('test');
