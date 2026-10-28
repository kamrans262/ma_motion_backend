<?php

use Illuminate\Support\Facades\Route;

// Opening the site root uses the admin login flow. Authenticated admins
// are redirected from that login route to the dashboard.
Route::redirect('/', '/admin/login');

require __DIR__.'/web/admin.php';
