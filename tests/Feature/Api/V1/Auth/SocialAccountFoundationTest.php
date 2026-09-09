<?php

namespace Tests\Feature\Api\V1\Auth;

use App\Features\Auth\Models\SocialAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SocialAccountFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_social_account_can_be_linked_to_user(): void
    {
        $user = User::factory()->create();

        SocialAccount::query()->create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_user_id' => 'provider-user-123',
            'provider_email' => $user->email,
        ]);

        $this->assertSame(1, $user->socialAccounts()->count());
        $this->assertDatabaseHas('social_accounts', [
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_user_id' => 'provider-user-123',
        ]);
    }
}
