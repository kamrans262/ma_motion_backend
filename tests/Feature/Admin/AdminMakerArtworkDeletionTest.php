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

class AdminMakerArtworkDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_deleting_maker_account_cleans_up_artwork_media_files(): void
    {
        Storage::fake('public');
        $admin=User::factory()->create(['role'=>UserRole::Admin,'status'=>UserStatus::Active]);
        $maker=User::factory()->create(['role'=>UserRole::Maker,'status'=>UserStatus::Active]);
        $artwork=Artwork::query()->create(['maker_id'=>$maker->id,'title'=>'Cleanup','moderation_status'=>'pending','is_visible'=>true,'sort_order'=>0]);
        Storage::disk('public')->put('artworks/cleanup.jpg','data');
        ArtworkMedia::query()->create(['artwork_id'=>$artwork->id,'kind'=>'image','disk'=>'public','path'=>'artworks/cleanup.jpg','mime_type'=>'image/jpeg','size_bytes'=>4,'width'=>1,'height'=>1,'sort_order'=>0,'is_primary'=>true]);

        $this->actingAs($admin)->delete('/admin/users/'.$maker->id)->assertRedirect('/admin/users');
        $this->assertDatabaseMissing('users',['id'=>$maker->id]);
        $this->assertDatabaseMissing('artworks',['id'=>$artwork->id]);
        $this->assertDatabaseCount('artwork_media',0);
        Storage::disk('public')->assertMissing('artworks/cleanup.jpg');
    }
}
