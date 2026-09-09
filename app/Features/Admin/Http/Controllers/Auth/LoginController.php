<?php

namespace App\Features\Admin\Http\Controllers\Auth;

use App\Features\Admin\Actions\AuthenticateAdminAction;
use App\Features\Admin\Http\Requests\AdminLoginRequest;
use App\Features\Auth\Enums\UserRole;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class LoginController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user !== null && $user->isActive() && $user->hasRole(UserRole::Admin)) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    public function store(AdminLoginRequest $request, AuthenticateAdminAction $action): RedirectResponse
    {
        $action->execute($request);

        return redirect()->intended(route('admin.dashboard'));
    }
}
