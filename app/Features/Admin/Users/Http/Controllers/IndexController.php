<?php

namespace App\Features\Admin\Users\Http\Controllers;

use App\Features\Admin\Users\Http\Requests\UserIndexRequest;
use App\Features\Admin\Users\Services\UserManagementService;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

final class IndexController extends Controller
{
    public function __invoke(UserIndexRequest $request, UserManagementService $users): View
    {
        $filters = $request->validated();

        return view('admin.users.index', [
            'users' => $users->paginate($filters),
            'roles' => $users->roles(),
            'statuses' => $users->statuses(),
            'filters' => $filters,
        ]);
    }
}
