<?php

namespace Tests\Feature\Api\V1\Makers;

use App\Features\Auth\Enums\UserRole;
use App\Features\Locations\Models\Location;
use App\Features\Taxonomy\Models\ArtworkStyle;
use App\Features\Taxonomy\Models\ArtworkType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MakerOnboardingProfileApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_maker_can_save_complete_mobile_onboarding_profile(): void
    {
        $maker = User::factory()->create(['role' => UserRole::Maker]);
        $token = $maker->createToken('mobile', ['mobile'])->plainTextToken;

        $type = ArtworkType::query()->create([
            'name' => 'Painting',
            'slug' => 'painting',
            'is_active' => true,
            'sort_order' => 10,
        ]);
        $style = ArtworkStyle::query()->create([
            'name' => 'Contemporary',
            'slug' => 'contemporary',
            'is_active' => true,
            'sort_order' => 10,
        ]);
        $location = Location::query()->create([
            'city' => 'Chicago',
            'region' => 'IL',
            'postal_code' => '60601',
            'country_code' => 'US',
            'latitude' => 41.8864,
            'longitude' => -87.6186,
            'is_active' => true,
            'sort_order' => 10,
        ]);

        $this->withToken($token)
            ->patchJson('/api/v1/me/maker-profile', [
                'name' => 'Artist Ken',
                'bio' => 'A contemporary artist exploring form and color.',
                'location_text' => 'Chicago, IL 60601',
                'location_id' => $location->id,
                'website_url' => 'www.artist.com',
                'contact_email' => 'contact@artist.com',
                'type_ids' => [$type->id],
                'style_ids' => [$style->id],
                'complete_onboarding' => true,
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Artist Ken')
            ->assertJsonPath('data.location', 'Chicago, IL 60601')
            ->assertJsonPath('data.managed_location.id', $location->id)
            ->assertJsonPath('data.website_url', 'https://www.artist.com')
            ->assertJsonPath('data.contact_email', 'contact@artist.com')
            ->assertJsonPath('data.types.0.id', $type->id)
            ->assertJsonPath('data.styles.0.id', $style->id)
            ->assertJsonPath('data.onboarding_completed', true);

        $profile = $maker->refresh()->makerProfile;

        $this->assertNotNull($profile);
        $this->assertNotNull($profile->onboarding_completed_at);
        $this->assertSame('https://www.artist.com', $profile->website_url);
        $this->assertSame('contact@artist.com', $profile->contact_email);
        $this->assertSame([$type->id], $profile->types()->pluck('artwork_types.id')->all());
        $this->assertSame([$style->id], $profile->styles()->pluck('artwork_styles.id')->all());
    }

    public function test_maker_can_read_private_onboarding_profile(): void
    {
        $maker = User::factory()->create(['role' => UserRole::Maker]);
        $profile = $maker->makerProfile()->firstOrCreate(['user_id' => $maker->id]);
        $profile->update([
            'website_url' => 'https://artist.example',
            'contact_email' => 'private@example.com',
        ]);
        $token = $maker->createToken('mobile', ['mobile'])->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/me/maker-profile')
            ->assertOk()
            ->assertJsonPath('data.website_url', 'https://artist.example')
            ->assertJsonPath('data.contact_email', 'private@example.com')
            ->assertJsonPath('data.onboarding_completed', false);
    }

    public function test_inactive_taxonomy_and_location_are_rejected(): void
    {
        $maker = User::factory()->create(['role' => UserRole::Maker]);
        $token = $maker->createToken('mobile', ['mobile'])->plainTextToken;

        $type = ArtworkType::query()->create([
            'name' => 'Inactive Type',
            'slug' => 'inactive-type',
            'is_active' => false,
            'sort_order' => 10,
        ]);
        $style = ArtworkStyle::query()->create([
            'name' => 'Inactive Style',
            'slug' => 'inactive-style',
            'is_active' => false,
            'sort_order' => 10,
        ]);
        $location = Location::query()->create([
            'city' => 'Inactive City',
            'country_code' => 'US',
            'is_active' => false,
            'sort_order' => 10,
        ]);

        $this->withToken($token)
            ->patchJson('/api/v1/me/maker-profile', [
                'location_id' => $location->id,
                'type_ids' => [$type->id],
                'style_ids' => [$style->id],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'location_id',
                'type_ids.0',
                'style_ids.0',
            ]);
    }

    public function test_private_contact_email_is_not_added_to_public_maker_contract(): void
    {
        $maker = User::factory()->create(['role' => UserRole::Maker]);
        $profile = $maker->makerProfile()->firstOrCreate(['user_id' => $maker->id]);
        $profile->update(['contact_email' => 'private@example.com']);

        $response = $this->getJson('/api/v1/makers/'.$maker->id)
            ->assertOk();

        $response->assertJsonMissing(['contact_email' => 'private@example.com']);
    }
}
