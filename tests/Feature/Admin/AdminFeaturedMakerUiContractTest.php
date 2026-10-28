<?php

namespace Tests\Feature\Admin;

use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminFeaturedMakerUiContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_featured_maker_admin_reuses_design_system_and_sidebar_has_polished_scrollbar(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'status' => UserStatus::Active]);
        $maker = User::factory()->create(['role' => UserRole::Maker, 'status' => UserStatus::Active]);

        $this->actingAs($admin)->get('/admin/featured-maker?maker_id='.$maker->id)
            ->assertOk()
            ->assertSee('ma-featured-maker-grid', false)
            ->assertSee('ma-featured-maker-selector', false)
            ->assertSee('ma-featured-maker-preview', false)
            ->assertSee('Save Featured Maker');

        $sidebar = file_get_contents(resource_path('views/admin/partials/sidebar.blade.php'));
        $css = file_get_contents(public_path('assets/admin/css/admin.css'));

        // Keep the backend page functional, but do not expose it in the admin sidebar.
        $this->assertStringNotContainsString("admin.featured-maker.index", $sidebar);
        $this->assertStringNotContainsString('<span>Featured Maker</span>', $sidebar);
        $this->assertStringContainsString('scrollbar-gutter: stable', $css);
        $this->assertStringContainsString('.ma-nav::-webkit-scrollbar-thumb', $css);
        $this->assertStringContainsString('.ma-featured-maker-preview', $css);
        $this->assertStringContainsString('@media (max-width: 720px)', $css);
    }
}
