<?php
namespace Tests\Feature\Admin;
use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Features\Taxonomy\Models\ArtworkStyle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class AdminStyleManagementTest extends TestCase { use RefreshDatabase;
 public function test_admin_can_manage_styles(): void {
  $admin=User::factory()->create(['role'=>UserRole::Admin,'status'=>UserStatus::Active]);
  $this->actingAs($admin)->post('/admin/styles',['name'=>'Neo Minimal','sort_order'=>30,'is_active'=>1])->assertRedirect('/admin/styles');
  $style=ArtworkStyle::where('slug','neo-minimal')->firstOrFail();
  $this->actingAs($admin)->get('/admin/styles?search=Neo')->assertOk()->assertSee('Neo Minimal');
  $this->actingAs($admin)->put('/admin/styles/'.$style->id,['name'=>'Neo Minimalist','sort_order'=>20,'is_active'=>0])->assertRedirect('/admin/styles/'.$style->id.'/edit');
  $this->assertFalse($style->fresh()->is_active);
  $this->actingAs($admin)->delete('/admin/styles/'.$style->id)->assertRedirect('/admin/styles');
  $this->assertSoftDeleted('artwork_styles',['id'=>$style->id]);
 }
}
