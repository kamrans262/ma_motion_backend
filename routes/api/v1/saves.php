<?php

use App\Features\Saves\Http\Controllers\Api\V1\DestroyController;
use App\Features\Saves\Http\Controllers\Api\V1\IndexController;
use App\Features\Saves\Http\Controllers\Api\V1\MakerStatisticsController;
use App\Features\Saves\Http\Controllers\Api\V1\StoreController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'account.active', 'role:appreciator'])->group(function (): void {
    Route::get('/me/saved-makers', IndexController::class)->name('saved-makers.index');
    Route::post('/me/saved-makers/{maker}', StoreController::class)->whereNumber('maker')->name('saved-makers.store');
    Route::delete('/me/saved-makers/{maker}', DestroyController::class)->whereNumber('maker')->name('saved-makers.destroy');
});

Route::middleware(['auth:sanctum', 'account.active', 'role:maker'])->group(function (): void {
    Route::get('/me/maker-statistics', MakerStatisticsController::class)->name('maker-statistics.show');
});
