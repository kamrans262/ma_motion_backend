<?php

namespace App\Features\Auth\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class RequireRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user instanceof User) {
            throw new AuthenticationException();
        }

        if (! $user->hasRole(...$roles)) {
            throw new AuthorizationException('You are not authorized to perform this action.');
        }

        return $next($request);
    }
}
