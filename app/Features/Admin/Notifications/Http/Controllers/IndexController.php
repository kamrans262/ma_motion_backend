<?php

namespace App\Features\Admin\Notifications\Http\Controllers;

use App\Features\Admin\Notifications\Http\Requests\NotificationIndexRequest;
use App\Features\Admin\Notifications\Services\NotificationManagementService;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

final class IndexController extends Controller
{
    public function __invoke(NotificationIndexRequest $request, NotificationManagementService $notifications): View
    {
        $filters = $request->validated();

        return view('admin.notifications.index', [
            'notifications' => $notifications->paginate($filters),
            'summary' => $notifications->summary(),
            'types' => $notifications->types(),
            'filters' => $filters,
        ]);
    }
}
