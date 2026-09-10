<?php

use App\Features\FeaturedMaker\Http\Controllers\Api\V1\ShowController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'account.active'])
    ->get('/featured-maker', ShowController::class)
    ->name('featured-maker.show');
