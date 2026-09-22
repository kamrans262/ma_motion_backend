<?php

use App\Features\Auth\Http\Controllers\Api\V1\AppreciatorOnboardingRegisterController;
use App\Features\Auth\Http\Controllers\Api\V1\CompleteAppreciatorExperienceOnboardingController;
use App\Features\Auth\Http\Controllers\Api\V1\ForgotPasswordController;
use App\Features\Auth\Http\Controllers\Api\V1\LoginController;
use App\Features\Auth\Http\Controllers\Api\V1\LogoutController;
use App\Features\Auth\Http\Controllers\Api\V1\MakerOnboardingRegisterController;
use App\Features\Auth\Http\Controllers\Api\V1\MeController;
use App\Features\Auth\Http\Controllers\Api\V1\RegisterController;
use App\Features\Auth\Http\Controllers\Api\V1\RequestEmailOtpController;
use App\Features\Auth\Http\Controllers\Api\V1\VerifyEmailOtpController;
use App\Features\Auth\Http\Controllers\Api\V1\ResetPasswordController;
use App\Features\Auth\Http\Controllers\Api\V1\SocialLoginController;
use App\Features\Auth\Http\Controllers\Api\V1\StartMakerExperienceOnboardingController;
use App\Features\Auth\Http\Controllers\Api\V1\SwitchExperienceController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->name('auth.')->group(function (): void {
    Route::post('/email-otp/request', RequestEmailOtpController::class)->middleware('throttle:auth-otp-request')->name('email-otp.request');
    Route::post('/email-otp/verify', VerifyEmailOtpController::class)->middleware('throttle:auth-otp-verify')->name('email-otp.verify');
    Route::post('/register', RegisterController::class)->middleware('throttle:auth-register')->name('register');
    Route::post('/maker-onboarding', MakerOnboardingRegisterController::class)->middleware('throttle:auth-register')->name('maker-onboarding');
    Route::post('/appreciator-onboarding', AppreciatorOnboardingRegisterController::class)->middleware('throttle:auth-register')->name('appreciator-onboarding');
    Route::post('/login', LoginController::class)->middleware('throttle:auth-login')->name('login');
    Route::post('/social/{provider}', SocialLoginController::class)->where('provider', 'google|apple')->middleware('throttle:auth-social')->name('social.login');
    Route::post('/forgot-password', ForgotPasswordController::class)->middleware('throttle:auth-password')->name('forgot-password');
    Route::post('/reset-password', ResetPasswordController::class)->middleware('throttle:auth-password')->name('reset-password');
});

Route::middleware(['auth:sanctum', 'account.active'])->group(function (): void {
    Route::get('/me', MeController::class)->name('me');
    Route::patch('/me/experience', SwitchExperienceController::class)->name('me.experience.switch');
    Route::post('/me/experience/maker/onboarding', StartMakerExperienceOnboardingController::class)
        ->name('me.experience.maker.onboarding');
    Route::post('/me/experience/appreciator/onboarding', CompleteAppreciatorExperienceOnboardingController::class)
        ->name('me.experience.appreciator.onboarding');
    Route::post('/auth/logout', LogoutController::class)->name('auth.logout');
});
