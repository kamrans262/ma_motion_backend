<?php

namespace Tests\Feature\Admin;

use App\Features\Artworks\Models\Artwork;
use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Features\Locations\Models\Location;
use App\Features\Shows\Models\Show;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AdminShowManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_search_edit_relate_hide_and_soft_delete_show(): void
    {
        $admin = User::factory()->create(['role'=>UserRole::Admin,'status'=>UserStatus::Active]);
        $maker = User::factory()->create(['name'=>'Gallery Maker','role'=>UserRole::Maker,'status'=>UserStatus::Active]);
        $location = Location::query()->create(['city'=>'Brooklyn','region'=>'NY','postal_code'=>'11201','country_code'=>'US','is_active'=>true,'sort_order'=>0]);
        $artwork = Artwork::query()->create(['maker_id'=>$maker->id,'title'=>'Blue Form','moderation_status'=>'approved','is_visible'=>true,'sort_order'=>0]);

        $create = $this->actingAs($admin)->post('/admin/shows', ['maker_id'=>$maker->id,'name'=>'Brooklyn Forms','description'=>'Initial','location_id'=>$location->id,'location_text'=>'Gallery One','start_date'=>'2026-10-01','end_date'=>'2026-10-20','sort_order'=>0]);
        $show = Show::query()->where('name','Brooklyn Forms')->firstOrFail();
        $create->assertRedirect('/admin/shows/'.$show->id)->assertSessionHas('status');

        $this->actingAs($admin)->get('/admin/shows?search=Brooklyn')->assertOk()->assertSee('Brooklyn Forms')->assertSee('Gallery Maker');
        $this->actingAs($admin)->put('/admin/shows/'.$show->id, ['name'=>'Brooklyn Forms Updated','description'=>'Edited','location_id'=>$location->id,'location_text'=>'Gallery Two','start_date'=>'2026-10-02','end_date'=>'2026-10-25','sort_order'=>4,'artwork_ids'=>[$artwork->id]])
            ->assertRedirect('/admin/shows/'.$show->id)->assertSessionHas('status');
        $this->actingAs($admin)->patch('/admin/shows/'.$show->id.'/visibility')->assertRedirect('/admin/shows/'.$show->id);

        $show->refresh();
        $this->assertSame('Brooklyn Forms Updated',$show->name);
        $this->assertFalse($show->is_visible);
        $this->assertTrue($show->artworks->contains($artwork));

        $this->actingAs($admin)->delete('/admin/shows/'.$show->id)->assertRedirect('/admin/shows');
        $this->assertSoftDeleted('shows',['id'=>$show->id]);
        $this->assertDatabaseHas('artworks',['id'=>$artwork->id]);
    }

    public function test_admin_cannot_attach_artwork_owned_by_another_maker(): void
    {
        $admin = User::factory()->create(['role'=>UserRole::Admin,'status'=>UserStatus::Active]);
        $maker = User::factory()->create(['role'=>UserRole::Maker,'status'=>UserStatus::Active]);
        $other = User::factory()->create(['role'=>UserRole::Maker,'status'=>UserStatus::Active]);
        $foreignArtwork = Artwork::query()->create(['maker_id'=>$other->id,'title'=>'Other Work','moderation_status'=>'approved','is_visible'=>true,'sort_order'=>0]);
        $show = Show::query()->create(['maker_id'=>$maker->id,'name'=>'Protected Show','start_date'=>'2026-10-01','end_date'=>'2026-10-10','is_visible'=>true,'sort_order'=>0]);

        $this->actingAs($admin)->put('/admin/shows/'.$show->id, ['name'=>'Protected Show','description'=>null,'location_id'=>null,'location_text'=>null,'start_date'=>'2026-10-01','end_date'=>'2026-10-10','sort_order'=>0,'artwork_ids'=>[$foreignArtwork->id]])
            ->assertSessionHasErrors('artwork_ids');
        $this->assertDatabaseMissing('show_artwork',['show_id'=>$show->id,'artwork_id'=>$foreignArtwork->id]);
    }
}
