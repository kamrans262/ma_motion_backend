<?php

namespace App\Features\Admin\Http\Middleware;

use App\Features\Auth\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class EnsureAdminPanelAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return redirect()->route('admin.login');
        }

        if (! $user->isActive() || ! $user->hasRole(UserRole::Admin)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('admin.login')
                ->withErrors(['email' => 'Administrator access is required.']);
        }

        return $next($request);
    }
}
