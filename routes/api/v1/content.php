<?php

use App\Features\Content\Http\Controllers\Api\V1\IndexController;
use App\Features\Content\Http\Controllers\Api\V1\ShowController;
use Illuminate\Support\Facades\Route;

Route::get('/content', IndexController::class)->name('content.index');
Route::get('/content/{slug}', ShowController::class)
    ->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*')
    ->name('content.show');
