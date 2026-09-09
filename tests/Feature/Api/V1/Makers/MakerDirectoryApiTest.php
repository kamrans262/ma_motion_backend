<?php

namespace Tests\Feature\Api\V1\Makers;

use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MakerDirectoryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_directory_returns_only_active_makers_without_email(): void
    {
        $active = User::factory()->create(['name' => 'Visible Maker', 'email' => 'private@example.com', 'role' => UserRole::Maker, 'status' => UserStatus::Active]);
        $active->makerProfile()->create(['bio' => 'Abstract artist', 'location_text' => 'London']);
        User::factory()->create(['name' => 'Hidden Maker', 'role' => UserRole::Maker, 'status' => UserStatus::Inactive]);
        User::factory()->create(['name' => 'Viewer', 'role' => UserRole::Appreciator, 'status' => UserStatus::Active]);

        $response = $this->getJson('/api/v1/makers?search=London')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.0.name', 'Visible Maker')
            ->assertJsonPath('data.0.location', 'London')
            ->assertJsonPath('meta.total', 1);

        $this->assertStringNotContainsString('private@example.com', $response->getContent());
        $this->assertStringNotContainsString('Hidden Maker', $response->getContent());
        $this->assertStringNotContainsString('Viewer', $response->getContent());
    }

    public function test_public_can_read_visible_maker_detail(): void
    {
        $maker = User::factory()->create(['name' => 'Detail Maker', 'role' => UserRole::Maker, 'status' => UserStatus::Active]);
        $maker->makerProfile()->create(['bio' => 'Maker biography']);

        $this->getJson('/api/v1/makers/'.$maker->id)
            ->assertOk()
            ->assertJsonPath('data.id', $maker->id)
            ->assertJsonPath('data.name', 'Detail Maker')
            ->assertJsonPath('data.bio', 'Maker biography');
    }

    public function test_inactive_or_non_maker_detail_is_not_exposed(): void
    {
        $inactive = User::factory()->create(['role' => UserRole::Maker, 'status' => UserStatus::Inactive]);
        $viewer = User::factory()->create(['role' => UserRole::Appreciator, 'status' => UserStatus::Active]);

        $this->getJson('/api/v1/makers/'.$inactive->id)->assertNotFound()->assertJsonPath('success', false);
        $this->getJson('/api/v1/makers/'.$viewer->id)->assertNotFound()->assertJsonPath('success', false);
    }
}
