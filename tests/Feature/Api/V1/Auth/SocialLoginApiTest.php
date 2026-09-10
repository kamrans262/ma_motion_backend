<?php

namespace Tests\Feature\Api\V1\Auth;

use App\Features\Auth\Contracts\SocialIdentityVerifier;
use App\Features\Auth\Data\SocialIdentity;
use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Features\Auth\Models\SocialAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SocialLoginApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_social_maker_is_created_linked_and_issued_token(): void
    {
        $this->fakeIdentity(new SocialIdentity('google', 'google-123', 'social@example.com', 'Social Maker', true));

        $response = $this->postJson('/api/v1/auth/social/google', [
            'id_token' => 'valid-token', 'role' => 'maker', 'name' => 'Social Maker', 'device_name' => 'Android',
        ]);

        $response->assertOk()->assertJsonPath('data.user.email', 'social@example.com')->assertJsonPath('data.user.role', 'maker');
        $this->assertNotEmpty($response->json('data.token'));
        $user = User::query()->where('email', 'social@example.com')->firstOrFail();
        $this->assertDatabaseHas('social_accounts', ['user_id'=>$user->id,'provider'=>'google','provider_user_id'=>'google-123']);
        $this->assertDatabaseHas('maker_profiles', ['user_id'=>$user->id]);
    }

    public function test_existing_linked_social_account_logs_in_without_role_or_name(): void
    {
        $user = User::factory()->create(['role'=>UserRole::Appreciator,'status'=>UserStatus::Active,'email'=>'linked@example.com']);
        SocialAccount::query()->create(['user_id'=>$user->id,'provider'=>'apple','provider_user_id'=>'apple-123','provider_email'=>'linked@example.com']);
        $this->fakeIdentity(new SocialIdentity('apple', 'apple-123', 'linked@example.com', null, true));

        $this->postJson('/api/v1/auth/social/apple', ['id_token'=>'valid-token'])
            ->assertOk()->assertJsonPath('data.user.id', $user->id)->assertJsonPath('data.user.role', 'appreciator');
    }

    public function test_social_login_rejects_inactive_account(): void
    {
        $user = User::factory()->create(['role'=>UserRole::Appreciator,'status'=>UserStatus::Inactive]);
        SocialAccount::query()->create(['user_id'=>$user->id,'provider'=>'google','provider_user_id'=>'inactive-sub','provider_email'=>$user->email]);
        $this->fakeIdentity(new SocialIdentity('google', 'inactive-sub', $user->email, null, true));

        $this->postJson('/api/v1/auth/social/google', ['id_token'=>'valid-token'])->assertForbidden();
    }

    public function test_social_login_does_not_link_admin_account(): void
    {
        User::factory()->create(['role'=>UserRole::Admin,'status'=>UserStatus::Active,'email'=>'admin-social@example.com']);
        $this->fakeIdentity(new SocialIdentity('google', 'admin-sub', 'admin-social@example.com', 'Admin', true));

        $this->postJson('/api/v1/auth/social/google', ['id_token'=>'valid-token'])->assertForbidden();
    }

    public function test_new_social_account_requires_verified_email_role_and_name(): void
    {
        $this->fakeIdentity(new SocialIdentity('google', 'no-email', null, null, false));
        $this->postJson('/api/v1/auth/social/google', ['id_token'=>'valid-token'])->assertUnprocessable()->assertJsonValidationErrors('id_token');

        $this->fakeIdentity(new SocialIdentity('google', 'new-sub', 'new@example.com', null, true));
        $this->postJson('/api/v1/auth/social/google', ['id_token'=>'valid-token'])->assertUnprocessable()->assertJsonValidationErrors('role');
    }

    private function fakeIdentity(SocialIdentity $identity): void
    {
        $this->app->instance(SocialIdentityVerifier::class, new class($identity) implements SocialIdentityVerifier {
            public function __construct(private readonly SocialIdentity $identity) {}
            public function verify(string $provider, string $idToken): SocialIdentity { return $this->identity; }
        });
    }
}
