<?php

use App\Features\Account\Http\Controllers\Api\V1\ChangePasswordController;
use App\Features\Account\Http\Controllers\Api\V1\DeleteAccountController;
use App\Features\Account\Http\Controllers\Api\V1\DeleteProfileImageController;
use App\Features\Account\Http\Controllers\Api\V1\UpdateProfileController;
use App\Features\Account\Http\Controllers\Api\V1\UpdateProfileImageController;
use App\Features\Appreciators\Http\Controllers\Api\V1\ShowMyAppreciatorProfileController;
use App\Features\Appreciators\Http\Controllers\Api\V1\UpdateMyAppreciatorProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'account.active'])
    ->prefix('me')
    ->name('me.')
    ->group(function (): void {
        Route::patch('/profile', UpdateProfileController::class)->name('profile.update');
        Route::put('/password', ChangePasswordController::class)->name('password.update');
        Route::delete('/account', DeleteAccountController::class)->name('account.destroy');

        Route::middleware('role:appreciator')->group(function (): void {
            Route::get('/appreciator-profile', ShowMyAppreciatorProfileController::class)
                ->name('appreciator-profile.show');
            Route::patch('/appreciator-profile', UpdateMyAppreciatorProfileController::class)
                ->name('appreciator-profile.update');
        });

        Route::middleware('role:maker')->group(function (): void {
            Route::post('/profile-image', UpdateProfileImageController::class)->name('profile-image.update');
            Route::delete('/profile-image', DeleteProfileImageController::class)->name('profile-image.destroy');
        });
    });
