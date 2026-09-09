<?php

use App\Http\Controllers\Api\V1\HealthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->name('api.v1.')
    ->group(function (): void {
        Route::get('/health', HealthController::class)->name('health');

        require __DIR__.'/api/v1/auth.php';
        require __DIR__.'/api/v1/makers.php';
    });
