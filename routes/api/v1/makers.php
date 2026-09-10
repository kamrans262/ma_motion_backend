<?php

use App\Features\Makers\Http\Controllers\Api\V1\ArtworkIndexController;
use App\Features\Makers\Http\Controllers\Api\V1\IndexController;
use App\Features\Makers\Http\Controllers\Api\V1\ShowController;
use App\Features\Makers\Http\Controllers\Api\V1\UpdateMyProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/makers', IndexController::class)->name('makers.index');
Route::get('/makers/{maker}', ShowController::class)->whereNumber('maker')->name('makers.show');
Route::get('/makers/{maker}/artworks', ArtworkIndexController::class)->whereNumber('maker')->name('makers.artworks.index');

Route::middleware(['auth:sanctum', 'account.active', 'role:maker'])->group(function (): void {
    Route::patch('/me/maker-profile', UpdateMyProfileController::class)->name('me.maker-profile.update');
});
