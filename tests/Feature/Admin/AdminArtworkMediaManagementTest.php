<?php

namespace Tests\Feature\Admin;

use App\Features\Artworks\Models\Artwork;
use App\Features\Artworks\Models\ArtworkMedia;
use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminArtworkMediaManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_change_primary_and_remove_non_last_media(): void
    {
        Storage::fake('public');
        $admin=User::factory()->create(['role'=>UserRole::Admin,'status'=>UserStatus::Active]);
        $maker=User::factory()->create(['role'=>UserRole::Maker,'status'=>UserStatus::Active]);
        $artwork=Artwork::query()->create(['maker_id'=>$maker->id,'title'=>'Media Admin','moderation_status'=>'approved','is_visible'=>true,'sort_order'=>0]);
        Storage::disk('public')->put('artworks/a.jpg','a'); Storage::disk('public')->put('artworks/b.jpg','b');
        $first=ArtworkMedia::query()->create(['artwork_id'=>$artwork->id,'kind'=>'image','disk'=>'public','path'=>'artworks/a.jpg','mime_type'=>'image/jpeg','size_bytes'=>1,'width'=>1,'height'=>1,'sort_order'=>0,'is_primary'=>true]);
        $second=ArtworkMedia::query()->create(['artwork_id'=>$artwork->id,'kind'=>'image','disk'=>'public','path'=>'artworks/b.jpg','mime_type'=>'image/jpeg','size_bytes'=>1,'width'=>1,'height'=>1,'sort_order'=>1,'is_primary'=>false]);
        $this->actingAs($admin)->patch('/admin/artworks/'.$artwork->id.'/media/'.$second->id.'/primary')->assertRedirect('/admin/artworks/'.$artwork->id);
        $this->assertDatabaseHas('artwork_media',['id'=>$second->id,'is_primary'=>true]);
        $this->actingAs($admin)->delete('/admin/artworks/'.$artwork->id.'/media/'.$first->id)->assertRedirect('/admin/artworks/'.$artwork->id);
        $this->assertDatabaseMissing('artwork_media',['id'=>$first->id]);
        Storage::disk('public')->assertMissing('artworks/a.jpg');
    }
}
