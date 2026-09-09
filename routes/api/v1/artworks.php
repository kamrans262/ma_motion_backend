<?php

use App\Features\Artworks\Http\Controllers\Api\V1\AddMediaController;
use App\Features\Artworks\Http\Controllers\Api\V1\DestroyController;
use App\Features\Artworks\Http\Controllers\Api\V1\DestroyMediaController;
use App\Features\Artworks\Http\Controllers\Api\V1\IndexController;
use App\Features\Artworks\Http\Controllers\Api\V1\SetPrimaryMediaController;
use App\Features\Artworks\Http\Controllers\Api\V1\ShowController;
use App\Features\Artworks\Http\Controllers\Api\V1\StoreController;
use App\Features\Artworks\Http\Controllers\Api\V1\UpdateController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'account.active', 'role:maker'])
    ->prefix('me/artworks')
    ->name('me.artworks.')
    ->group(function (): void {
        Route::get('/', IndexController::class)->name('index');
        Route::post('/', StoreController::class)->name('store');
        Route::get('/{artwork}', ShowController::class)->whereNumber('artwork')->name('show');
        Route::patch('/{artwork}', UpdateController::class)->whereNumber('artwork')->name('update');
        Route::delete('/{artwork}', DestroyController::class)->whereNumber('artwork')->name('destroy');
        Route::post('/{artwork}/media', AddMediaController::class)->whereNumber('artwork')->name('media.store');
        Route::patch('/{artwork}/media/{media}/primary', SetPrimaryMediaController::class)->whereNumber(['artwork', 'media'])->name('media.primary');
        Route::delete('/{artwork}/media/{media}', DestroyMediaController::class)->whereNumber(['artwork', 'media'])->name('media.destroy');
    });
