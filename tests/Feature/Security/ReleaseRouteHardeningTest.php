<?php

namespace Tests\Feature\Security;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

final class ReleaseRouteHardeningTest extends TestCase
{
    public function test_social_and_discovery_routes_have_dedicated_rate_limits(): void
    {
        $social = Route::getRoutes()->getByName('api.v1.auth.social.login');
        $discovery = Route::getRoutes()->getByName('api.v1.discovery.index');
        $this->assertNotNull($social);
        $this->assertNotNull($discovery);
        $this->assertContains('throttle:auth-social', $social->gatherMiddleware());
        $this->assertContains('throttle:public-discovery', $discovery->gatherMiddleware());
    }

    public function test_admin_route_shadow_directory_remains_absent(): void
    {
        $this->assertDirectoryDoesNotExist(public_path('admin'));
    }
}
