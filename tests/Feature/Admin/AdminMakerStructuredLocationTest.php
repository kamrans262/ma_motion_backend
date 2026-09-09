<?php
namespace Tests\Feature\Admin;
use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Features\Locations\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class AdminMakerStructuredLocationTest extends TestCase { use RefreshDatabase;
 public function test_admin_can_link_maker_to_managed_location_without_breaking_location_text(): void {
  $admin=User::factory()->create(['role'=>UserRole::Admin,'status'=>UserStatus::Active]);
  $maker=User::factory()->create(['role'=>UserRole::Maker,'status'=>UserStatus::Active]);
  $location=Location::create(['city'=>'Los Angeles','region'=>'CA','postal_code'=>'90012','country_code'=>'US','latitude'=>34.0537,'longitude'=>-118.2428,'is_active'=>true,'sort_order'=>10]);
  $this->actingAs($admin)->put('/admin/makers/'.$maker->id,['name'=>$maker->name,'email'=>$maker->email,'status'=>'active','bio'=>'Maker bio','location_text'=>'Downtown Los Angeles','location_id'=>$location->id])->assertRedirect('/admin/makers/'.$maker->id);
  $this->assertDatabaseHas('maker_profiles',['user_id'=>$maker->id,'location_id'=>$location->id,'location_text'=>'Downtown Los Angeles']);
  $this->actingAs($admin)->get('/admin/makers/'.$maker->id)->assertOk()->assertSee('Los Angeles, CA, 90012, US')->assertSee('Downtown Los Angeles');
 }
}
