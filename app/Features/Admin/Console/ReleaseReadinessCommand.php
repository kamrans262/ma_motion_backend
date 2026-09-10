<?php

namespace App\Features\Admin\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

final class ReleaseReadinessCommand extends Command
{
    protected $signature = 'ma:release:check {--production : Enforce production-only requirements}';
    protected $description = 'Run MA Motion backend release-readiness checks without exposing secrets.';

    public function handle(): int
    {
        $production = (bool) $this->option('production');
        $failures = [];
        $warnings = [];

        $this->components->info('MA Motion backend release readiness');

        foreach ((array) config('ma_motion.release.required_php_extensions', []) as $extension) {
            if (! extension_loaded((string) $extension)) $failures[] = 'Required PHP extension is missing: '.$extension;
        }

        if ((string) config('app.key') === '') $failures[] = 'APP_KEY is missing.';
        try { DB::select('select 1'); } catch (Throwable) { $failures[] = 'Database connectivity check failed.'; }
        if (is_dir(public_path('admin'))) $failures[] = 'public/admin exists and can shadow Laravel Admin routes.';
        if (! is_writable(storage_path())) $failures[] = 'storage directory is not writable.';
        if (! is_writable(base_path('bootstrap/cache'))) $failures[] = 'bootstrap/cache is not writable.';

        foreach (['google', 'apple'] as $provider) {
            $enabled = (bool) config('ma_motion.social.'.$provider.'.enabled', false);
            $clientIds = (array) config('ma_motion.social.'.$provider.'.client_ids', []);
            if ($enabled && $clientIds === []) $failures[] = ucfirst($provider).' social login is enabled but no client ID is configured.';
            if (! $enabled) $warnings[] = ucfirst($provider).' social login is disabled until provider client IDs are configured.';
        }

        if ($production) {
            if (! app()->environment('production')) $failures[] = 'Application environment is not production.';
            if ((bool) config('app.debug')) $failures[] = 'APP_DEBUG must be false in production.';
            if (! str_starts_with((string) config('app.url'), 'https://')) $failures[] = 'APP_URL must use HTTPS in production.';
            if (! (bool) config('session.secure')) $failures[] = 'SESSION_SECURE_COOKIE must be enabled in production.';
        } else {
            $warnings[] = 'Production-only checks were not enforced. Run ma:release:check --production on the deployment host.';
        }

        foreach ($warnings as $warning) $this->components->warn($warning);
        foreach ($failures as $failure) $this->components->error($failure);

        if ($failures !== []) {
            $this->components->error(count($failures).' release-readiness check(s) failed.');
            return self::FAILURE;
        }

        $this->components->info('Release-readiness checks passed for the current environment.');
        return self::SUCCESS;
    }
}
