<?php

namespace App\Features\Admin\Analytics\Http\Controllers;

use App\Features\Admin\Analytics\Http\Requests\AnalyticsIndexRequest;
use App\Features\Admin\Analytics\Services\AdminAnalyticsService;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

final class IndexController extends Controller
{
    public function __invoke(AnalyticsIndexRequest $request, AdminAnalyticsService $service): View
    {
        return view('admin.analytics.index', $service->report($request->days()));
    }
}
