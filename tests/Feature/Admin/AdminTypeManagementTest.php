<?php
namespace Tests\Feature\Admin;
use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Features\Taxonomy\Models\ArtworkType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class AdminTypeManagementTest extends TestCase { use RefreshDatabase;
 private function admin(): User { return User::factory()->create(['role'=>UserRole::Admin,'status'=>UserStatus::Active]); }
 public function test_admin_can_create_search_update_toggle_and_soft_delete_type(): void {
  $admin=$this->admin();
  $this->actingAs($admin)->post('/admin/types',['name'=>'Mixed Media','sort_order'=>25,'is_active'=>1])->assertRedirect('/admin/types')->assertSessionHas('status');
  $type=ArtworkType::where('slug','mixed-media')->firstOrFail();
  $this->actingAs($admin)->get('/admin/types?search=Mixed&status=active')->assertOk()->assertSee('Mixed Media');
  $this->actingAs($admin)->put('/admin/types/'.$type->id,['name'=>'Mixed Media Art','sort_order'=>15,'is_active'=>1])->assertRedirect('/admin/types/'.$type->id.'/edit');
  $this->actingAs($admin)->patch('/admin/types/'.$type->id.'/status')->assertRedirect();
  $this->assertFalse($type->fresh()->is_active);
  $this->actingAs($admin)->delete('/admin/types/'.$type->id)->assertRedirect('/admin/types');
  $this->assertSoftDeleted('artwork_types',['id'=>$type->id]);
 }
}
