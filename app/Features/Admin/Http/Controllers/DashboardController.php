<?php

namespace App\Features\Admin\Http\Controllers;

use App\Features\Admin\Services\AdminDashboardService;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

final class DashboardController extends Controller
{
    public function __invoke(AdminDashboardService $dashboard): View
    {
        return view('admin.dashboard.index', $dashboard->metrics());
    }
}
