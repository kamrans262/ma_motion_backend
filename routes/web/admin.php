<?php

use App\Features\Admin\Http\Controllers\Auth\LoginController;
use App\Features\Admin\Http\Controllers\Auth\LogoutController;
use App\Features\Admin\Http\Controllers\DashboardController;
use App\Features\Admin\Makers\Http\Controllers\IndexController as MakerIndexController;
use App\Features\Admin\Makers\Http\Controllers\ShowController as MakerShowController;
use App\Features\Admin\Makers\Http\Controllers\UpdateController as MakerUpdateController;
use App\Features\Admin\Users\Http\Controllers\DestroyController as UserDestroyController;
use App\Features\Admin\Users\Http\Controllers\IndexController as UserIndexController;
use App\Features\Admin\Users\Http\Controllers\ShowController as UserShowController;
use App\Features\Admin\Users\Http\Controllers\UpdateController as UserUpdateController;
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

            Route::prefix('users')->name('users.')->group(function (): void {
                Route::get('/', UserIndexController::class)->name('index');
                Route::get('/{user}', UserShowController::class)->whereNumber('user')->name('show');
                Route::put('/{user}', UserUpdateController::class)->whereNumber('user')->name('update');
                Route::delete('/{user}', UserDestroyController::class)->whereNumber('user')->name('destroy');
            });

            Route::prefix('makers')->name('makers.')->group(function (): void {
                Route::get('/', MakerIndexController::class)->name('index');
                Route::get('/{maker}', MakerShowController::class)->whereNumber('maker')->name('show');
                Route::put('/{maker}', MakerUpdateController::class)->whereNumber('maker')->name('update');
            });

            Route::post('/logout', LogoutController::class)->name('logout');
        });
    });
