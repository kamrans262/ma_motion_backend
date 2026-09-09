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
}
