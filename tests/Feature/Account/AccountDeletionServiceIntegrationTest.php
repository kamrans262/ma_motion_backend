<?php

namespace Tests\Feature\Account;

use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class AccountDeletionServiceIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_existing_admin_user_deletion_flow_also_cleans_maker_profile_image(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role'=>UserRole::Admin,'status'=>UserStatus::Active]);
        $maker = User::factory()->create(['role'=>UserRole::Maker,'status'=>UserStatus::Active]);
        $maker->makerProfile()->create(['profile_image_path'=>'maker-profiles/admin-delete.jpg']);
        Storage::disk('public')->put('maker-profiles/admin-delete.jpg','image');

        $this->actingAs($admin)->delete('/admin/users/'.$maker->id)->assertRedirect('/admin/users');
        Storage::disk('public')->assertMissing('maker-profiles/admin-delete.jpg');
        $this->assertDatabaseMissing('users', ['id'=>$maker->id]);
    }
}
