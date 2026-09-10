<?php

namespace Tests\Feature\Security;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

class NamedRateLimiterCoverageTest extends TestCase
{
    public function test_every_named_route_rate_limiter_is_registered(): void
    {
        $referenced = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(base_path('routes')));

        foreach ($iterator as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $contents = file_get_contents($file->getPathname());
            preg_match_all('/throttle:([A-Za-z0-9_.-]+)/', $contents ?: '', $matches);
            $referenced = array_merge($referenced, $matches[1] ?? []);
        }

        $provider = file_get_contents(app_path('Providers/AppServiceProvider.php')) ?: '';
        preg_match_all('/RateLimiter::for\([\'\"]([^\'\"]+)[\'\"]/', $provider, $registeredMatches);

        $referenced = array_values(array_unique($referenced));
        $registered = array_values(array_unique($registeredMatches[1] ?? []));
        $missing = array_values(array_diff($referenced, $registered));

        sort($referenced);
        sort($registered);
        sort($missing);

        $this->assertNotEmpty($referenced, 'No named route rate limiters were discovered; the coverage test may be broken.');
        $this->assertSame([], $missing, 'Missing named rate limiter registrations: '.implode(', ', $missing));
        $this->assertContains('admin-login', $registered);
        $this->assertContains('auth-social', $registered);
        $this->assertContains('public-discovery', $registered);
    }
}
