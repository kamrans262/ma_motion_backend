<?php

use App\Features\Discovery\Http\Controllers\Api\V1\ArtworkIndexController;
use App\Features\Discovery\Http\Controllers\Api\V1\FilterOptionsController;
use App\Features\Discovery\Http\Controllers\Api\V1\LocationIndexController;
use App\Features\Discovery\Http\Controllers\Api\V1\MakerIndexController;
use App\Features\Discovery\Http\Controllers\Api\V1\SearchController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:public-discovery')->group(function (): void {
    Route::get('/discovery', ArtworkIndexController::class)->name('discovery.index');
    Route::prefix('discovery')->name('discovery.')->group(function (): void {
        Route::get('/artworks', ArtworkIndexController::class)->name('artworks.index');
        Route::get('/makers', MakerIndexController::class)->name('makers.index');
        Route::get('/search', SearchController::class)->name('search');
        Route::get('/filters', FilterOptionsController::class)->name('filters.show');
        Route::get('/locations', LocationIndexController::class)->name('locations.index');
    });
});
