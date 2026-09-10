<?php

namespace Tests\Feature\Admin;

use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class AdminSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_own_profile(): void
    {
        $admin = User::factory()->create(['name'=>'Old Admin','email'=>'old-admin@example.com','role'=>UserRole::Admin,'status'=>UserStatus::Active]);

        $this->actingAs($admin)->put('/admin/settings/profile', ['name'=>'MA Admin','email'=>'ADMIN@EXAMPLE.COM'])->assertRedirect();
        $this->assertSame('MA Admin', $admin->fresh()->name);
        $this->assertSame('admin@example.com', $admin->fresh()->email);
    }

    public function test_admin_can_change_password_and_api_tokens_are_revoked(): void
    {
        $admin = User::factory()->create(['password'=>'OldAdmin12','role'=>UserRole::Admin,'status'=>UserStatus::Active]);
        $admin->createToken('admin-api',['mobile']);

        $this->actingAs($admin)->put('/admin/settings/password', ['current_password'=>'OldAdmin12','password'=>'NewAdmin34','password_confirmation'=>'NewAdmin34'])->assertRedirect();
        $this->assertTrue(Hash::check('NewAdmin34', $admin->fresh()->password));
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_wrong_admin_current_password_is_rejected(): void
    {
        $admin = User::factory()->create(['password'=>'OldAdmin12','role'=>UserRole::Admin,'status'=>UserStatus::Active]);
        $this->actingAs($admin)->from('/admin/settings')->put('/admin/settings/password', ['current_password'=>'WrongPass1','password'=>'NewAdmin34','password_confirmation'=>'NewAdmin34'])->assertRedirect('/admin/settings')->assertSessionHasErrors('current_password');
    }
}
