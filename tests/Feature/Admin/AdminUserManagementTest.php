<?php

namespace Tests\Feature\Admin;

use App\Features\Appreciators\Models\AppreciatorProfile;
use App\Features\Artworks\Models\Artwork;
use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Features\Saves\Models\ArtworkSave;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_search_and_filter_users(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'status' => UserStatus::Active]);
        User::factory()->create(['name' => 'Maker Alpha', 'email' => 'alpha@example.com', 'role' => UserRole::Maker]);
        User::factory()->create(['name' => 'Appreciator Beta', 'email' => 'beta@example.com', 'role' => UserRole::Appreciator]);

        $this->actingAs($admin)
            ->get('/admin/users?search=Alpha&role=maker&status=active')
            ->assertOk()
            ->assertSee('Maker Alpha')
            ->assertDontSee('Appreciator Beta');
    }

    public function test_admin_can_update_user_and_deactivation_revokes_tokens(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'status' => UserStatus::Active]);
        $user = User::factory()->create(['role' => UserRole::Appreciator, 'status' => UserStatus::Active]);
        $user->createToken('mobile', ['mobile']);

        $this->actingAs($admin)
            ->put('/admin/users/'.$user->id, [
                'name' => 'Updated User',
                'email' => 'UPDATED@example.com',
                'status' => UserStatus::Inactive->value,
            ])
            ->assertRedirect('/admin/users/'.$user->id)
            ->assertSessionHas('status');

        $user->refresh();
        $this->assertSame('Updated User', $user->name);
        $this->assertSame('updated@example.com', $user->email);
        $this->assertSame(UserStatus::Inactive, $user->status);
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_current_admin_cannot_deactivate_self(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'status' => UserStatus::Active]);

        $this->actingAs($admin)
            ->from('/admin/users/'.$admin->id)
            ->put('/admin/users/'.$admin->id, [
                'name' => $admin->name,
                'email' => $admin->email,
                'status' => UserStatus::Inactive->value,
            ])
            ->assertRedirect('/admin/users/'.$admin->id)
            ->assertSessionHasErrors('status');

        $this->assertTrue($admin->fresh()->isActive());
    }

    public function test_admin_can_delete_non_admin_but_not_administrator_account(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'status' => UserStatus::Active]);
        $appreciator = User::factory()->create(['role' => UserRole::Appreciator]);

        $this->actingAs($admin)
            ->delete('/admin/users/'.$appreciator->id)
            ->assertRedirect('/admin/users');
        $this->assertDatabaseMissing('users', ['id' => $appreciator->id]);

        $otherAdmin = User::factory()->create(['role' => UserRole::Admin]);
        $this->actingAs($admin)
            ->from('/admin/users/'.$otherAdmin->id)
            ->delete('/admin/users/'.$otherAdmin->id)
            ->assertRedirect('/admin/users/'.$otherAdmin->id)
            ->assertSessionHasErrors('user');
        $this->assertDatabaseHas('users', ['id' => $otherAdmin->id]);
    }

    public function test_admin_appreciator_view_shows_location_and_saved_artwork(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'status' => UserStatus::Active]);
        $appreciator = User::factory()->create([
            'name' => 'Appreciator Viewer',
            'email' => 'viewer@example.com',
            'role' => UserRole::Appreciator,
            'status' => UserStatus::Active,
        ]);
        $maker = User::factory()->create([
            'name' => 'Saved Maker',
            'role' => UserRole::Maker,
            'status' => UserStatus::Active,
        ]);

        AppreciatorProfile::query()->create([
            'user_id' => $appreciator->id,
            'location_text' => 'Chicago, IL',
            'onboarding_completed_at' => now(),
        ]);

        $artwork = Artwork::query()->create([
            'maker_id' => $maker->id,
            'title' => 'Saved Canvas',
        ]);

        ArtworkSave::query()->create([
            'user_id' => $appreciator->id,
            'artwork_id' => $artwork->id,
        ]);

        $this->actingAs($admin)
            ->get('/admin/users/'.$appreciator->id)
            ->assertOk()
            ->assertSee('Appreciator Viewer')
            ->assertSee('viewer@example.com')
            ->assertSee('Chicago, IL')
            ->assertSee('Saved artworks')
            ->assertSee('Saved Canvas')
            ->assertSee('Saved Maker')
            ->assertSee(route('admin.artworks.show', $artwork), false);
    }
}
