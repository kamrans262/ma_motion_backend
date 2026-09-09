<?php

namespace Tests\Feature\Admin;

use App\Features\Artworks\Models\Artwork;
use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Features\Taxonomy\Models\ArtworkType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminArtworkManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_search_edit_moderate_hide_and_soft_delete_artwork(): void
    {
        $admin=User::factory()->create(['role'=>UserRole::Admin,'status'=>UserStatus::Active]);
        $maker=User::factory()->create(['name'=>'Gallery Maker','role'=>UserRole::Maker,'status'=>UserStatus::Active]);
        $type=ArtworkType::query()->create(['name'=>'Painting','slug'=>'painting','is_active'=>true,'sort_order'=>0]);
        $artwork=Artwork::query()->create(['maker_id'=>$maker->id,'title'=>'Blue Study','description'=>'Original','moderation_status'=>'pending','is_visible'=>true,'sort_order'=>0]);

        $this->actingAs($admin)->get('/admin/artworks?search=Blue')->assertOk()->assertSee('Blue Study')->assertSee('Gallery Maker');
        $this->actingAs($admin)->put('/admin/artworks/'.$artwork->id,['title'=>'Blue Study Updated','description'=>'Edited','artwork_type_id'=>$type->id,'artwork_style_id'=>null,'location_id'=>null,'location_text'=>'Brooklyn','sort_order'=>4])->assertRedirect('/admin/artworks/'.$artwork->id)->assertSessionHas('status');
        $this->actingAs($admin)->patch('/admin/artworks/'.$artwork->id.'/moderation',['moderation_status'=>'approved'])->assertRedirect('/admin/artworks/'.$artwork->id);
        $this->actingAs($admin)->patch('/admin/artworks/'.$artwork->id.'/visibility')->assertRedirect('/admin/artworks/'.$artwork->id);

        $artwork->refresh();
        $this->assertSame('Blue Study Updated',$artwork->title);
        $this->assertSame('approved',$artwork->moderation_status->value);
        $this->assertFalse($artwork->is_visible);
        $this->assertSame($type->id,$artwork->artwork_type_id);

        $this->actingAs($admin)->delete('/admin/artworks/'.$artwork->id)->assertRedirect('/admin/artworks');
        $this->assertSoftDeleted('artworks',['id'=>$artwork->id]);
    }

    public function test_rejection_requires_reason(): void
    {
        $admin=User::factory()->create(['role'=>UserRole::Admin,'status'=>UserStatus::Active]);
        $maker=User::factory()->create(['role'=>UserRole::Maker,'status'=>UserStatus::Active]);
        $artwork=Artwork::query()->create(['maker_id'=>$maker->id,'title'=>'Review Me','moderation_status'=>'pending','is_visible'=>true,'sort_order'=>0]);
        $this->actingAs($admin)->patch('/admin/artworks/'.$artwork->id.'/moderation',['moderation_status'=>'rejected','rejection_reason'=>''])->assertSessionHasErrors('rejection_reason');
    }
}
