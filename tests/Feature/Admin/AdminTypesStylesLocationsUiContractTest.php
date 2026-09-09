<?php
namespace Tests\Feature\Admin;
use Illuminate\Support\Facades\File;
use Tests\TestCase;
class AdminTypesStylesLocationsUiContractTest extends TestCase {
 public function test_m06_admin_pages_reuse_existing_design_system_and_sidebar_navigation(): void {
  $sidebar=File::get(resource_path('views/admin/partials/sidebar.blade.php'));
  $taxonomy=File::get(resource_path('views/admin/taxonomy/index.blade.php'));
  $locations=File::get(resource_path('views/admin/locations/index.blade.php'));
  $this->assertStringContainsString("route('admin.types.index')",$sidebar);
  $this->assertStringContainsString("route('admin.styles.index')",$sidebar);
  $this->assertStringContainsString("route('admin.locations.index')",$sidebar);
  foreach(['ma-detail-grid','ma-panel','ma-field','ma-table','ma-button'] as $class){ $this->assertStringContainsString($class,$taxonomy); $this->assertStringContainsString($class,$locations); }
  $this->assertDirectoryDoesNotExist(public_path('admin'));
  $this->assertFileExists(public_path('assets/admin/css/admin.css'));
  $css=File::get(public_path('assets/admin/css/admin.css'));
  $this->assertMatchesRegularExpression('/\.ma-detail-grid\s*\{[^}]*margin-bottom:\s*var\(--ma-space-6\);/s',$css);
 }
}
