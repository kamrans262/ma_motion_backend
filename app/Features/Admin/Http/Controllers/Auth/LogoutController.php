<?php

namespace App\Features\Admin\Http\Controllers\Auth;

use App\Features\Admin\Actions\LogoutAdminAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class LogoutController extends Controller
{
    public function __invoke(Request $request, LogoutAdminAction $action): RedirectResponse
    {
        $action->execute($request);

        return redirect()->route('admin.login');
    }
}
