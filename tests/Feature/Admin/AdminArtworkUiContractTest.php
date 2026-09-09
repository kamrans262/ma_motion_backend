<?php

namespace Tests\Feature\Admin;

use App\Features\Artworks\Models\Artwork;
use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminArtworkUiContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_artwork_admin_reuses_existing_design_system_and_sidebar_navigation(): void
    {
        $admin=User::factory()->create(['role'=>UserRole::Admin,'status'=>UserStatus::Active]);
        $maker=User::factory()->create(['role'=>UserRole::Maker,'status'=>UserStatus::Active]);
        $artwork=Artwork::query()->create(['maker_id'=>$maker->id,'title'=>'UI Contract','moderation_status'=>'pending','is_visible'=>true,'sort_order'=>0]);
        $this->actingAs($admin)->get('/admin/artworks')->assertOk()->assertSee('Artwork directory')->assertSee('admin/artworks',false);
        $this->actingAs($admin)->get('/admin/artworks/'.$artwork->id)->assertOk()->assertSee('Publishing control')->assertSee('Artwork images');
        $sidebar=file_get_contents(resource_path('views/admin/partials/sidebar.blade.php'));
        $css=file_get_contents(public_path('assets/admin/css/admin.css'));
        $this->assertStringContainsString("admin.artworks.index",$sidebar);
        $this->assertStringContainsString('.ma-artwork-media-grid',$css);
        $this->assertStringContainsString('var(--ma-space-6)',$css);
        $this->assertDirectoryDoesNotExist(public_path('admin'));
    }
}
