<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

require __DIR__.'/web/admin.php';
