<?php
namespace Tests\Feature\Admin;
use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Features\Locations\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class AdminLocationManagementTest extends TestCase { use RefreshDatabase;
 public function test_admin_can_manage_structured_locations(): void {
  $admin=User::factory()->create(['role'=>UserRole::Admin,'status'=>UserStatus::Active]);
  $payload=['city'=>'Brooklyn','region'=>'NY','postal_code'=>'11201','country_code'=>'us','latitude'=>40.6943,'longitude'=>-73.9918,'sort_order'=>10,'is_active'=>1];
  $this->actingAs($admin)->post('/admin/locations',$payload)->assertRedirect('/admin/locations');
  $location=Location::firstOrFail(); $this->assertSame('US',$location->country_code);
  $this->actingAs($admin)->get('/admin/locations?search=11201&country_code=US')->assertOk()->assertSee('Brooklyn');
  $payload['city']='New York';
  $this->actingAs($admin)->put('/admin/locations/'.$location->id,$payload)->assertRedirect('/admin/locations/'.$location->id.'/edit');
  $this->assertSame('New York',$location->fresh()->city);
  $this->actingAs($admin)->patch('/admin/locations/'.$location->id.'/status')->assertRedirect(); $this->assertFalse($location->fresh()->is_active);
  $this->actingAs($admin)->delete('/admin/locations/'.$location->id)->assertRedirect('/admin/locations');
  $this->assertSoftDeleted('locations',['id'=>$location->id]);
 }
 public function test_coordinates_must_be_provided_as_a_pair(): void {
  $admin=User::factory()->create(['role'=>UserRole::Admin,'status'=>UserStatus::Active]);
  $this->actingAs($admin)->post('/admin/locations',['city'=>'Test','country_code'=>'US','latitude'=>40,'sort_order'=>0,'is_active'=>1])->assertSessionHasErrors('longitude');
 }
}
