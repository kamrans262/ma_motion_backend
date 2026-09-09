<?php

namespace Tests\Feature\Admin;

use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminMakerManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_maker_directory_excludes_non_makers_and_supports_search(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'status' => UserStatus::Active]);
        $maker = User::factory()->create(['name' => 'Sculptor One', 'role' => UserRole::Maker]);
        $maker->makerProfile()->create(['location_text' => 'Brooklyn']);
        User::factory()->create(['name' => 'Viewer Two', 'role' => UserRole::Appreciator]);

        $this->actingAs($admin)
            ->get('/admin/makers?search=Brooklyn')
            ->assertOk()
            ->assertSee('Sculptor One')
            ->assertDontSee('Viewer Two');
    }

    public function test_admin_can_update_maker_profile_and_status(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'status' => UserStatus::Active]);
        $maker = User::factory()->create(['role' => UserRole::Maker, 'status' => UserStatus::Active]);
        $maker->createToken('mobile', ['mobile']);

        $this->actingAs($admin)
            ->put('/admin/makers/'.$maker->id, [
                'name' => 'Updated Maker',
                'email' => 'MAKER@example.com',
                'status' => UserStatus::Inactive->value,
                'bio' => 'A focused contemporary maker.',
                'location_text' => 'New York, NY',
            ])
            ->assertRedirect('/admin/makers/'.$maker->id)
            ->assertSessionHas('status');

        $maker->refresh();
        $this->assertSame('Updated Maker', $maker->name);
        $this->assertSame('maker@example.com', $maker->email);
        $this->assertSame(UserStatus::Inactive, $maker->status);
        $this->assertSame('New York, NY', $maker->makerProfile->location_text);
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_non_maker_cannot_be_opened_as_maker(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $appreciator = User::factory()->create(['role' => UserRole::Appreciator]);

        $this->actingAs($admin)
            ->get('/admin/makers/'.$appreciator->id)
            ->assertNotFound();
    }
}
