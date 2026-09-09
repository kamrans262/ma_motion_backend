<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        RateLimiter::for('auth-register', static function (Request $request): Limit {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('auth-login', static function (Request $request): Limit {
            $email = Str::lower(trim((string) $request->input('email')));

            return Limit::perMinute(5)->by($email.'|'.$request->ip());
        });

        RateLimiter::for('auth-password', static function (Request $request): Limit {
            return Limit::perMinute(3)->by($request->ip());
        });

        RateLimiter::for('admin-login', static function (Request $request): Limit {
            $email = Str::lower(trim((string) $request->input('email')));

            return Limit::perMinute(5)->by($email.'|'.$request->ip());
        });
    }
}
