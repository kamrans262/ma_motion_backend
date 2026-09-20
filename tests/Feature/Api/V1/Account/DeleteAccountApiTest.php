<?php

namespace Tests\Feature\Api\V1\Account;

use App\Features\Artworks\Models\Artwork;
use App\Features\Artworks\Models\ArtworkMedia;
use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class DeleteAccountApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_maker_can_delete_own_account_and_owned_files_are_cleaned_up(): void
    {
        Storage::fake('public');
        $maker = User::factory()->create(['password'=>'DeleteMe1','role'=>UserRole::Maker,'status'=>UserStatus::Active]);
        $maker->makerProfile()->create(['profile_image_path'=>'maker-profiles/profile.jpg']);
        Storage::disk('public')->put('maker-profiles/profile.jpg', 'profile');
        $artwork = Artwork::query()->create(['maker_id'=>$maker->id,'title'=>'Owned','moderation_status'=>'approved','is_visible'=>true,'sort_order'=>0]);
        ArtworkMedia::query()->create(['artwork_id'=>$artwork->id,'kind'=>'image','disk'=>'public','path'=>'artworks/owned.jpg','mime_type'=>'image/jpeg','size_bytes'=>4,'width'=>1,'height'=>1,'sort_order'=>0,'is_primary'=>true]);
        Storage::disk('public')->put('artworks/owned.jpg', 'data');
        $token = $maker->createToken('mobile',['mobile'])->plainTextToken;

        $this->withToken($token)->deleteJson('/api/v1/me/account', ['current_password'=>'DeleteMe1','confirmation'=>'DELETE'])->assertOk();

        $this->assertDatabaseMissing('users', ['id'=>$maker->id]);
        $this->assertDatabaseMissing('artworks', ['id'=>$artwork->id]);
        Storage::disk('public')->assertMissing('maker-profiles/profile.jpg');
        Storage::disk('public')->assertMissing('artworks/owned.jpg');
    }

    public function test_delete_account_requires_valid_password_and_confirmation(): void
    {
        $user = User::factory()->create(['password'=>'KeepMe123','role'=>UserRole::Appreciator,'status'=>UserStatus::Active]);
        $token = $user->createToken('mobile',['mobile'])->plainTextToken;

        $this->withToken($token)->deleteJson('/api/v1/me/account', ['current_password'=>'WrongPass1','confirmation'=>'DELETE'])->assertUnprocessable()->assertJsonValidationErrors('current_password');
        $this->withToken($token)->deleteJson('/api/v1/me/account', ['current_password'=>'KeepMe123','confirmation'=>'NO'])->assertUnprocessable()->assertJsonValidationErrors('confirmation');
        $this->assertDatabaseHas('users', ['id'=>$user->id]);
    }

    public function test_passwordless_maker_can_confirm_deletion_and_both_profiles_and_content_are_removed(): void
    {
        Storage::fake('public');

        $maker = User::factory()->create([
            'password' => null,
            'role' => UserRole::Maker,
            'status' => UserStatus::Active,
        ]);
        $profile = $maker->makerProfile()->create();
        $maker->appreciatorProfile()->create(['location_text' => 'Chicago']);
        $profile->contents()->create([
            'slot' => 1,
            'kind' => 'video',
            'path' => 'maker-profiles/'.$maker->id.'/content/salon.mp4',
            'mime_type' => 'video/mp4',
        ]);
        Storage::disk('public')->put('maker-profiles/'.$maker->id.'/content/salon.mp4', 'video');
        $token = $maker->createToken('mobile', ['mobile'])->plainTextToken;

        $this->withToken($token)->deleteJson('/api/v1/me/account', [
            'confirmation' => 'NO',
        ])->assertUnprocessable()->assertJsonValidationErrors('confirmation');

        $this->assertDatabaseHas('users', ['id' => $maker->id]);

        $this->withToken($token)->deleteJson('/api/v1/me/account', [
            'confirmation' => 'DELETE',
        ])->assertOk();

        $this->assertDatabaseMissing('users', ['id' => $maker->id]);
        $this->assertDatabaseMissing('maker_profile_contents', [
            'path' => 'maker-profiles/'.$maker->id.'/content/salon.mp4',
        ]);
        Storage::disk('public')->assertMissing('maker-profiles/'.$maker->id.'/content/salon.mp4');
        $this->withToken($token)->getJson('/api/v1/auth/me')->assertUnauthorized();
    }

    public function test_passwordless_appreciator_can_confirm_deletion_without_a_password(): void
    {
        $viewer = User::factory()->create([
            'password' => null,
            'role' => UserRole::Appreciator,
            'status' => UserStatus::Active,
        ]);
        $viewer->appreciatorProfile()->create(['location_text' => 'New York']);
        $token = $viewer->createToken('mobile', ['mobile'])->plainTextToken;

        $this->withToken($token)->deleteJson('/api/v1/me/account', [
            'confirmation' => 'DELETE',
        ])->assertOk();

        $this->assertDatabaseMissing('users', ['id' => $viewer->id]);
    }

    public function test_password_based_accounts_still_require_current_password(): void
    {
        $viewer = User::factory()->create([
            'password' => 'KeepMe123',
            'role' => UserRole::Appreciator,
            'status' => UserStatus::Active,
        ]);
        $token = $viewer->createToken('mobile', ['mobile'])->plainTextToken;

        $this->withToken($token)->deleteJson('/api/v1/me/account', [
            'confirmation' => 'DELETE',
        ])->assertUnprocessable()->assertJsonValidationErrors('current_password');
        $this->assertDatabaseHas('users', ['id' => $viewer->id]);
    }

    public function test_delete_account_requires_authentication(): void
    {
        $this->deleteJson('/api/v1/me/account', ['current_password'=>'Anything1','confirmation'=>'DELETE'])->assertUnauthorized();
    }
}
