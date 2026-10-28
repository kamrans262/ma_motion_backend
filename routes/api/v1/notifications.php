<?php

use App\Features\Notifications\Http\Controllers\Api\V1\IndexController;
use App\Features\Notifications\Http\Controllers\Api\V1\RegisterPushTokenController;
use App\Features\Notifications\Http\Controllers\Api\V1\UnregisterPushTokenController;
use App\Features\Notifications\Http\Controllers\Api\V1\MarkAllReadController;
use App\Features\Notifications\Http\Controllers\Api\V1\MarkReadController;
use App\Features\Notifications\Http\Controllers\Api\V1\ShowSettingsController;
use App\Features\Notifications\Http\Controllers\Api\V1\UpdateSettingsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'account.active'])
    ->prefix('me')
    ->name('me.')
    ->group(function (): void {
        Route::post('/push-tokens', RegisterPushTokenController::class)->name('push-tokens.store');
        Route::delete('/push-tokens', UnregisterPushTokenController::class)->name('push-tokens.destroy');
        Route::get('/notifications', IndexController::class)->name('notifications.index');
        Route::patch('/notifications/read-all', MarkAllReadController::class)->name('notifications.read-all');
        Route::patch('/notifications/{notification}/read', MarkReadController::class)
            ->whereNumber('notification')
            ->name('notifications.read');
        Route::get('/notification-settings', ShowSettingsController::class)->name('notification-settings.show');
        Route::patch('/notification-settings', UpdateSettingsController::class)->name('notification-settings.update');
    });
