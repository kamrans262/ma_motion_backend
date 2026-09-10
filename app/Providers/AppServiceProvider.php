<?php

namespace App\Providers;

use App\Features\Auth\Contracts\SocialIdentityVerifier;
use App\Features\Auth\Services\OidcIdTokenVerifier;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SocialIdentityVerifier::class, OidcIdTokenVerifier::class);
    }

    public function boot(): void
    {
        RateLimiter::for('auth-register', static fn (Request $request): Limit => Limit::perMinute(5)->by($request->ip()));

        RateLimiter::for('auth-login', static function (Request $request): Limit {
            $email = Str::lower(trim((string) $request->input('email')));

            return Limit::perMinute(5)->by($email.'|'.$request->ip());
        });

        RateLimiter::for('auth-password', static fn (Request $request): Limit => Limit::perMinute(3)->by($request->ip()));

        // Preserved from the verified Admin foundation (Milestone 04+).
        // M15 extends rate limiting; it must never replace/remove existing named limiters.
        RateLimiter::for('admin-login', static function (Request $request): Limit {
            $email = Str::lower(trim((string) $request->input('email')));

            return Limit::perMinute(5)->by($email.'|'.$request->ip());
        });

        RateLimiter::for('auth-social', static fn (Request $request): Limit => Limit::perMinute(10)->by($request->ip()));
        RateLimiter::for('public-discovery', static fn (Request $request): Limit => Limit::perMinute(120)->by($request->ip()));
    }
}
