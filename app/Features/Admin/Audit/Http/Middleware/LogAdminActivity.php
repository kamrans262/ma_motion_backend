<?php

namespace App\Features\Admin\Audit\Http\Middleware;

use App\Features\Admin\Audit\Services\AdminAuditRecorder;
use App\Features\Auth\Enums\UserRole;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class LogAdminActivity
{
    public function __construct(private readonly AdminAuditRecorder $recorder) {}

    public function handle(Request $request, Closure $next): Response
    {
        $admin = $request->user();
        $response = $next($request);

        if ($request->isMethodSafe()) {
            return $response;
        }

        if (! $admin instanceof User) {
            $admin = $request->user();
        }

        if ($admin instanceof User && $admin->hasRole(UserRole::Admin)) {
            $this->recorder->record($request, $admin, $response);
        }

        return $response;
    }
}
