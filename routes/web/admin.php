<?php

use App\Features\Admin\Artworks\Http\Controllers\DestroyController as ArtworkDestroyController;
use App\Features\Admin\Artworks\Http\Controllers\DestroyMediaController as ArtworkDestroyMediaController;
use App\Features\Admin\Artworks\Http\Controllers\IndexController as ArtworkIndexController;
use App\Features\Admin\Artworks\Http\Controllers\ModerateController as ArtworkModerateController;
use App\Features\Admin\Artworks\Http\Controllers\SetPrimaryMediaController as ArtworkSetPrimaryMediaController;
use App\Features\Admin\Artworks\Http\Controllers\ShowController as ArtworkShowController;
use App\Features\Admin\Artworks\Http\Controllers\ToggleVisibilityController as ArtworkToggleVisibilityController;
use App\Features\Admin\Artworks\Http\Controllers\UpdateController as ArtworkUpdateController;
use App\Features\Admin\FeaturedMaker\Http\Controllers\DestroyController as FeaturedMakerDestroyController;
use App\Features\Admin\FeaturedMaker\Http\Controllers\IndexController as FeaturedMakerIndexController;
use App\Features\Admin\FeaturedMaker\Http\Controllers\UpdateController as FeaturedMakerUpdateController;
use App\Features\Admin\Http\Controllers\Auth\LoginController;
use App\Features\Admin\Http\Controllers\Auth\LogoutController;
use App\Features\Admin\Http\Controllers\DashboardController;
use App\Features\Admin\Locations\Http\Controllers\DestroyController as LocationDestroyController;
use App\Features\Admin\Locations\Http\Controllers\EditController as LocationEditController;
use App\Features\Admin\Locations\Http\Controllers\IndexController as LocationIndexController;
use App\Features\Admin\Locations\Http\Controllers\StoreController as LocationStoreController;
use App\Features\Admin\Locations\Http\Controllers\ToggleStatusController as LocationToggleStatusController;
use App\Features\Admin\Locations\Http\Controllers\UpdateController as LocationUpdateController;
use App\Features\Admin\Notifications\Http\Controllers\IndexController as NotificationIndexController;
use App\Features\Admin\Makers\Http\Controllers\IndexController as MakerIndexController;
use App\Features\Admin\Makers\Http\Controllers\ShowController as MakerShowController;
use App\Features\Admin\Makers\Http\Controllers\UpdateController as MakerUpdateController;
use App\Features\Admin\Saves\Http\Controllers\IndexController as SaveIndexController;
use App\Features\Admin\Shows\Http\Controllers\DestroyController as ShowDestroyController;
use App\Features\Admin\Shows\Http\Controllers\IndexController as ShowIndexController;
use App\Features\Admin\Shows\Http\Controllers\ShowController as AdminShowController;
use App\Features\Admin\Shows\Http\Controllers\StoreController as ShowStoreController;
use App\Features\Admin\Shows\Http\Controllers\ToggleVisibilityController as ShowToggleVisibilityController;
use App\Features\Admin\Shows\Http\Controllers\UpdateController as ShowUpdateController;
use App\Features\Admin\Taxonomy\Styles\Http\Controllers\DestroyController as StyleDestroyController;
use App\Features\Admin\Taxonomy\Styles\Http\Controllers\EditController as StyleEditController;
use App\Features\Admin\Taxonomy\Styles\Http\Controllers\IndexController as StyleIndexController;
use App\Features\Admin\Taxonomy\Styles\Http\Controllers\StoreController as StyleStoreController;
use App\Features\Admin\Taxonomy\Styles\Http\Controllers\ToggleStatusController as StyleToggleStatusController;
use App\Features\Admin\Taxonomy\Styles\Http\Controllers\UpdateController as StyleUpdateController;
use App\Features\Admin\Taxonomy\Types\Http\Controllers\DestroyController as TypeDestroyController;
use App\Features\Admin\Taxonomy\Types\Http\Controllers\EditController as TypeEditController;
use App\Features\Admin\Taxonomy\Types\Http\Controllers\IndexController as TypeIndexController;
use App\Features\Admin\Taxonomy\Types\Http\Controllers\StoreController as TypeStoreController;
use App\Features\Admin\Taxonomy\Types\Http\Controllers\ToggleStatusController as TypeToggleStatusController;
use App\Features\Admin\Taxonomy\Types\Http\Controllers\UpdateController as TypeUpdateController;
use App\Features\Admin\Users\Http\Controllers\DestroyController as UserDestroyController;
use App\Features\Admin\Users\Http\Controllers\IndexController as UserIndexController;
use App\Features\Admin\Users\Http\Controllers\ShowController as UserShowController;
use App\Features\Admin\Users\Http\Controllers\UpdateController as UserUpdateController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:admin-login')->name('login.store');

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

        Route::get('/saves', SaveIndexController::class)->name('saves.index');
        Route::get('/notifications', NotificationIndexController::class)->name('notifications.index');

        Route::prefix('featured-maker')->name('featured-maker.')->group(function (): void {
            Route::get('/', FeaturedMakerIndexController::class)->name('index');
            Route::put('/', FeaturedMakerUpdateController::class)->name('update');
            Route::delete('/', FeaturedMakerDestroyController::class)->name('destroy');
        });

        Route::prefix('artworks')->name('artworks.')->group(function (): void {
            Route::get('/', ArtworkIndexController::class)->name('index');
            Route::get('/{artwork}', ArtworkShowController::class)->whereNumber('artwork')->name('show');
            Route::put('/{artwork}', ArtworkUpdateController::class)->whereNumber('artwork')->name('update');
            Route::patch('/{artwork}/moderation', ArtworkModerateController::class)->whereNumber('artwork')->name('moderate');
            Route::patch('/{artwork}/visibility', ArtworkToggleVisibilityController::class)->whereNumber('artwork')->name('toggle-visibility');
            Route::patch('/{artwork}/media/{media}/primary', ArtworkSetPrimaryMediaController::class)->whereNumber(['artwork', 'media'])->name('media.primary');
            Route::delete('/{artwork}/media/{media}', ArtworkDestroyMediaController::class)->whereNumber(['artwork', 'media'])->name('media.destroy');
            Route::delete('/{artwork}', ArtworkDestroyController::class)->whereNumber('artwork')->name('destroy');
        });

        Route::prefix('shows')->name('shows.')->group(function (): void {
            Route::get('/', ShowIndexController::class)->name('index');
            Route::post('/', ShowStoreController::class)->name('store');
            Route::get('/{show}', AdminShowController::class)->whereNumber('show')->name('show');
            Route::put('/{show}', ShowUpdateController::class)->whereNumber('show')->name('update');
            Route::patch('/{show}/visibility', ShowToggleVisibilityController::class)->whereNumber('show')->name('toggle-visibility');
            Route::delete('/{show}', ShowDestroyController::class)->whereNumber('show')->name('destroy');
        });

        Route::prefix('types')->name('types.')->group(function (): void {
            Route::get('/', TypeIndexController::class)->name('index');
            Route::post('/', TypeStoreController::class)->name('store');
            Route::get('/{type}/edit', TypeEditController::class)->whereNumber('type')->name('edit');
            Route::put('/{type}', TypeUpdateController::class)->whereNumber('type')->name('update');
            Route::patch('/{type}/status', TypeToggleStatusController::class)->whereNumber('type')->name('toggle-status');
            Route::delete('/{type}', TypeDestroyController::class)->whereNumber('type')->name('destroy');
        });

        Route::prefix('styles')->name('styles.')->group(function (): void {
            Route::get('/', StyleIndexController::class)->name('index');
            Route::post('/', StyleStoreController::class)->name('store');
            Route::get('/{style}/edit', StyleEditController::class)->whereNumber('style')->name('edit');
            Route::put('/{style}', StyleUpdateController::class)->whereNumber('style')->name('update');
            Route::patch('/{style}/status', StyleToggleStatusController::class)->whereNumber('style')->name('toggle-status');
            Route::delete('/{style}', StyleDestroyController::class)->whereNumber('style')->name('destroy');
        });

        Route::prefix('locations')->name('locations.')->group(function (): void {
            Route::get('/', LocationIndexController::class)->name('index');
            Route::post('/', LocationStoreController::class)->name('store');
            Route::get('/{location}/edit', LocationEditController::class)->whereNumber('location')->name('edit');
            Route::put('/{location}', LocationUpdateController::class)->whereNumber('location')->name('update');
            Route::patch('/{location}/status', LocationToggleStatusController::class)->whereNumber('location')->name('toggle-status');
            Route::delete('/{location}', LocationDestroyController::class)->whereNumber('location')->name('destroy');
        });

        Route::post('/logout', LogoutController::class)->name('logout');
    });
});
