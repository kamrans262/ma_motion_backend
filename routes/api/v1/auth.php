<?php

use App\Features\Auth\Http\Controllers\Api\V1\ForgotPasswordController;
use App\Features\Auth\Http\Controllers\Api\V1\LoginController;
use App\Features\Auth\Http\Controllers\Api\V1\LogoutController;
use App\Features\Auth\Http\Controllers\Api\V1\MakerOnboardingRegisterController;
use App\Features\Auth\Http\Controllers\Api\V1\MeController;
use App\Features\Auth\Http\Controllers\Api\V1\RegisterController;
use App\Features\Auth\Http\Controllers\Api\V1\ResetPasswordController;
use App\Features\Auth\Http\Controllers\Api\V1\SocialLoginController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->name('auth.')->group(function (): void {
    Route::post('/register', RegisterController::class)->middleware('throttle:auth-register')->name('register');
    Route::post('/maker-onboarding', MakerOnboardingRegisterController::class)->middleware('throttle:auth-register')->name('maker-onboarding');
    Route::post('/login', LoginController::class)->middleware('throttle:auth-login')->name('login');
    Route::post('/social/{provider}', SocialLoginController::class)->where('provider', 'google|apple')->middleware('throttle:auth-social')->name('social.login');
    Route::post('/forgot-password', ForgotPasswordController::class)->middleware('throttle:auth-password')->name('forgot-password');
    Route::post('/reset-password', ResetPasswordController::class)->middleware('throttle:auth-password')->name('reset-password');
});

Route::middleware(['auth:sanctum', 'account.active'])->group(function (): void {
    Route::get('/me', MeController::class)->name('me');
    Route::post('/auth/logout', LogoutController::class)->name('auth.logout');
});
