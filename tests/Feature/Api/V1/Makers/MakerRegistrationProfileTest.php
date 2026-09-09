<?php

namespace Tests\Feature\Api\V1\Makers;

use App\Features\Auth\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MakerRegistrationProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_maker_registration_creates_profile_record(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'New Maker',
            'email' => 'newmaker@example.com',
            'password' => 'Secure123',
            'password_confirmation' => 'Secure123',
            'role' => UserRole::Maker->value,
        ])->assertCreated();

        $maker = User::query()->where('email', 'newmaker@example.com')->firstOrFail();
        $this->assertDatabaseHas('maker_profiles', ['user_id' => $maker->id]);
    }

    public function test_appreciator_registration_does_not_create_maker_profile(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'New Appreciator',
            'email' => 'newappreciator@example.com',
            'password' => 'Secure123',
            'password_confirmation' => 'Secure123',
            'role' => UserRole::Appreciator->value,
        ])->assertCreated();

        $user = User::query()->where('email', 'newappreciator@example.com')->firstOrFail();
        $this->assertDatabaseMissing('maker_profiles', ['user_id' => $user->id]);
    }
}
