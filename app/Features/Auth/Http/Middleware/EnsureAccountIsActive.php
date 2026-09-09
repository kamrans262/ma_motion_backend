<?php

namespace App\Features\Auth\Http\Middleware;

use App\Features\Auth\Exceptions\InactiveAccountException;
use App\Models\User;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureAccountIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof User) {
            throw new AuthenticationException();
        }

        if (! $user->isActive()) {
            throw new InactiveAccountException();
        }

        return $next($request);
    }
}
