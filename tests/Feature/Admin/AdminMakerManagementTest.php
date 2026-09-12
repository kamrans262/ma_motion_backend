<?php

namespace Tests\Feature\Admin;

use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

    public function test_admin_can_preview_and_manage_maker_salon_image_and_content_slots(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => UserRole::Admin, 'status' => UserStatus::Active]);
        $maker = User::factory()->create(['role' => UserRole::Maker, 'status' => UserStatus::Active]);

        $this->actingAs($admin)
            ->post('/admin/makers/'.$maker->id.'/profile-image', [
                'image' => UploadedFile::fake()->image('salon.jpg', 1200, 900),
            ])
            ->assertRedirect('/admin/makers/'.$maker->id);

        $maker->refresh();
        $this->assertNotNull($maker->makerProfile?->profile_image_path);
        Storage::disk('public')->assertExists($maker->makerProfile->profile_image_path);

        $this->actingAs($admin)
            ->post('/admin/makers/'.$maker->id.'/content/2', [
                'caption' => 'Admin content two',
                'media' => UploadedFile::fake()->image('content-two.jpg'),
            ])
            ->assertRedirect('/admin/makers/'.$maker->id);

        $this->actingAs($admin)
            ->get('/admin/makers/'.$maker->id)
            ->assertOk()
            ->assertSee('Maker salon image')
            ->assertSee('Content 1 / 2 / 3')
            ->assertSee('Admin content two');

        $content = $maker->makerProfile->contents()->where('slot', 2)->firstOrFail();
        Storage::disk('public')->assertExists($content->path);

        $this->actingAs($admin)
            ->delete('/admin/makers/'.$maker->id.'/content/2')
            ->assertRedirect('/admin/makers/'.$maker->id);

        Storage::disk('public')->assertMissing($content->path);
        $this->assertDatabaseMissing('maker_profile_contents', ['id' => $content->id]);

        $profileImagePath = $maker->refresh()->makerProfile->profile_image_path;

        $this->actingAs($admin)
            ->delete('/admin/makers/'.$maker->id.'/profile-image')
            ->assertRedirect('/admin/makers/'.$maker->id);

        Storage::disk('public')->assertMissing($profileImagePath);
        $this->assertNull($maker->refresh()->makerProfile->profile_image_path);
    }

    public function test_admin_can_manage_maker_visibility_settings(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'status' => UserStatus::Active]);
        $maker = User::factory()->create(['role' => UserRole::Maker, 'status' => UserStatus::Active]);

        $this->actingAs($admin)
            ->put('/admin/makers/'.$maker->id, [
                'name' => $maker->name,
                'email' => $maker->email,
                'status' => UserStatus::Active->value,
                'website_url' => 'https://artist.example',
                'contact_email' => 'contact@artist.example',
                'show_website_on_info_page' => 0,
                'show_email_on_info_page' => 1,
                'show_shows_on_info_page' => 0,
            ])
            ->assertRedirect('/admin/makers/'.$maker->id);

        $profile = $maker->refresh()->makerProfile;

        $this->assertFalse($profile->show_website_on_info_page);
        $this->assertTrue($profile->show_email_on_info_page);
        $this->assertFalse($profile->show_shows_on_info_page);
        $this->assertSame('https://artist.example', $profile->website_url);
        $this->assertSame('contact@artist.example', $profile->contact_email);
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
