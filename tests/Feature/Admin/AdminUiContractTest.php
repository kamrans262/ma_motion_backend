<?php

namespace Tests\Feature\Admin;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class AdminUiContractTest extends TestCase
{
    public function test_admin_css_encodes_ma_motion_design_and_responsive_contract(): void
    {
        $css = File::get(public_path('assets/admin/css/admin.css'));

        $this->assertStringContainsString('--ma-bg: #062317;', $css);
        $this->assertStringContainsString('--ma-purple: #8b3dff;', $css);
        $this->assertStringContainsString('"Instrument Sans", Arial, sans-serif', $css);
        $this->assertStringContainsString('font-family: Arial, Helvetica, sans-serif;', $css);
        $this->assertStringContainsString('@media (max-width: 1023px)', $css);
        $this->assertStringContainsString('@media (max-width: 440px)', $css);
        $this->assertStringContainsString('prefers-reduced-motion', $css);
    }

    public function test_admin_login_contains_mobile_viewport_accessibility_and_font_asset(): void
    {
        $response = $this->get('/admin/login')->assertOk();
        $html = $response->getContent();

        $this->assertStringContainsString('width=device-width, initial-scale=1', $html);
        $this->assertStringContainsString('Skip to content', $html);
        $this->assertStringContainsString('fonts.googleapis.com/css2?family=Instrument+Sans', $html);
        $this->assertStringContainsString('/assets/admin/css/admin.css', $html);
    }
}
