<?php

namespace Tests\Feature\Admin;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class AdminUsersMakersUiContractTest extends TestCase
{
    public function test_users_and_makers_ui_reuses_ma_motion_responsive_design_system(): void
    {
        $css = File::get(public_path('assets/admin/css/admin.css'));

        $this->assertStringContainsString('.ma-filter-grid', $css);
        $this->assertStringContainsString('.ma-detail-grid', $css);
        $this->assertStringContainsString('.ma-pagination', $css);
        $this->assertStringContainsString('@media (max-width: 720px)', $css);
        $this->assertStringContainsString('"Instrument Sans", Arial, sans-serif', $css);
        $this->assertStringContainsString('font-family: Arial, Helvetica, sans-serif;', $css);
    }

    public function test_maker_profile_visibility_controls_and_salon_remove_button_are_aligned(): void
    {
        $css = File::get(public_path('assets/admin/css/admin.css'));
        $view = File::get(resource_path('views/admin/makers/show.blade.php'));

        $this->assertStringContainsString('.ma-maker-visibility-option input[type="checkbox"]', $css);
        $this->assertStringContainsString('width: 20px;', $css);
        $this->assertSame(3, substr_count($view, '<label class="ma-maker-visibility-option">'));
        $this->assertStringContainsString('ma-button ma-button--outline ma-button--full" type="submit">Remove salon image', $view);
        $this->assertStringContainsString('name="show_website_on_info_page" value="0"', $view);
        $this->assertStringContainsString('name="show_email_on_info_page" value="0"', $view);
        $this->assertStringContainsString('name="show_shows_on_info_page" value="0"', $view);
    }
}
