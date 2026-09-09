<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;

class AdminRouteCollisionRegressionTest extends TestCase
{
    public function test_public_admin_directory_does_not_shadow_admin_route(): void
    {
        $this->assertDirectoryDoesNotExist(public_path('admin'));
        $this->assertFileExists(public_path('assets/admin/css/admin.css'));
        $this->assertFileExists(public_path('assets/admin/js/admin.js'));
    }

    public function test_guest_admin_dashboard_request_reaches_laravel_and_redirects_to_login(): void
    {
        $this->get('/admin')
            ->assertRedirect(route('admin.login'));
    }

    public function test_admin_login_uses_non_conflicting_asset_urls(): void
    {
        $html = $this->get('/admin/login')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('/assets/admin/css/admin.css', $html);

        // Guard specifically against the old direct /admin/css/... asset path.
        // Do not use a plain substring assertion because the correct path
        // /assets/admin/css/admin.css legitimately contains /admin/css/admin.css.
        $this->assertDoesNotMatchRegularExpression(
            '~href=["\'](?:https?://[^/"\']+)?/admin/css/admin\.css(?:\?[^"\']*)?["\']~i',
            $html,
        );
    }
}
