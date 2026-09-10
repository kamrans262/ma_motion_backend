<?php

namespace Tests\Feature\Api\V1\Account;

use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class ChangePasswordApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_change_password_and_other_tokens_are_revoked(): void
    {
        $user = User::factory()->create(['password'=>'OldPassword1','role'=>UserRole::Appreciator,'status'=>UserStatus::Active]);
        $current = $user->createToken('current',['mobile']);
        $user->createToken('other',['mobile']);

        $this->withToken($current->plainTextToken)->putJson('/api/v1/me/password', [
            'current_password'=>'OldPassword1','password'=>'NewPassword2','password_confirmation'=>'NewPassword2',
        ])->assertOk();

        $this->assertTrue(Hash::check('NewPassword2', $user->fresh()->password));
        $this->assertDatabaseCount('personal_access_tokens', 1);
        $this->assertDatabaseHas('personal_access_tokens', ['id'=>$current->accessToken->id]);
    }

    public function test_incorrect_current_password_is_rejected(): void
    {
        $user = User::factory()->create(['password'=>'OldPassword1','role'=>UserRole::Appreciator,'status'=>UserStatus::Active]);
        $token = $user->createToken('mobile',['mobile'])->plainTextToken;

        $this->withToken($token)->putJson('/api/v1/me/password', [
            'current_password'=>'WrongPassword1','password'=>'NewPassword2','password_confirmation'=>'NewPassword2',
        ])->assertUnprocessable()->assertJsonValidationErrors('current_password');
    }
}
