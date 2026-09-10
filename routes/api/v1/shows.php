<?php

use App\Features\Shows\Http\Controllers\Api\V1\DestroyController;
use App\Features\Shows\Http\Controllers\Api\V1\IndexController;
use App\Features\Shows\Http\Controllers\Api\V1\PublicIndexController;
use App\Features\Shows\Http\Controllers\Api\V1\PublicShowController;
use App\Features\Shows\Http\Controllers\Api\V1\ShowController;
use App\Features\Shows\Http\Controllers\Api\V1\StoreController;
use App\Features\Shows\Http\Controllers\Api\V1\UpdateController;
use Illuminate\Support\Facades\Route;

Route::get('/makers/{maker}/shows', PublicIndexController::class)
    ->whereNumber('maker')
    ->name('makers.shows.index');
Route::get('/makers/{maker}/shows/{show}', PublicShowController::class)
    ->whereNumber(['maker', 'show'])
    ->name('makers.shows.show');

Route::middleware(['auth:sanctum', 'account.active', 'role:maker'])
    ->prefix('me/shows')
    ->name('me.shows.')
    ->group(function (): void {
        Route::get('/', IndexController::class)->name('index');
        Route::post('/', StoreController::class)->name('store');
        Route::get('/{show}', ShowController::class)->whereNumber('show')->name('show');
        Route::patch('/{show}', UpdateController::class)->whereNumber('show')->name('update');
        Route::delete('/{show}', DestroyController::class)->whereNumber('show')->name('destroy');
    });
