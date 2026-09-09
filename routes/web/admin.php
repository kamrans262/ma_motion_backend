<?php

use App\Features\Admin\Http\Controllers\Auth\LoginController;
use App\Features\Admin\Http\Controllers\Auth\LogoutController;
use App\Features\Admin\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/login', [LoginController::class, 'create'])->name('login');
        Route::post('/login', [LoginController::class, 'store'])
            ->middleware('throttle:admin-login')
            ->name('login.store');

        Route::middleware('admin.access')->group(function (): void {
            Route::get('/', DashboardController::class)->name('dashboard');
            Route::post('/logout', LogoutController::class)->name('logout');
        });
    });
