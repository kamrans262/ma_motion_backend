<?php
namespace Tests\Feature\Admin;
use App\Features\Taxonomy\Models\ArtworkStyle;
use App\Features\Taxonomy\Models\ArtworkType;
use Database\Seeders\MaMotionTaxonomySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class MaMotionTaxonomySeederTest extends TestCase { use RefreshDatabase;
 public function test_scope_taxonomy_defaults_are_seeded_idempotently(): void {
  $this->seed(MaMotionTaxonomySeeder::class); $this->seed(MaMotionTaxonomySeeder::class);
  $this->assertSame(17,ArtworkType::count()); $this->assertSame(12,ArtworkStyle::count());
  $this->assertTrue(ArtworkType::where('name','Painting')->exists()); $this->assertTrue(ArtworkStyle::where('name','Contemporary')->exists());
 }
}
