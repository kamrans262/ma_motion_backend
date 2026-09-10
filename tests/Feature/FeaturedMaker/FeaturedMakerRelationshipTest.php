<?php

namespace Tests\Feature\FeaturedMaker;

use App\Features\Artworks\Enums\ArtworkModerationStatus;
use App\Features\Artworks\Models\Artwork;
use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Features\FeaturedMaker\Models\FeaturedMakerSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeaturedMakerRelationshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_featured_maker_setting_connects_maker_artwork_and_admin(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'status' => UserStatus::Active]);
        $maker = User::factory()->create(['role' => UserRole::Maker, 'status' => UserStatus::Active]);
        $artwork = Artwork::query()->create([
            'maker_id' => $maker->id,
            'title' => 'Spotlight Piece',
            'moderation_status' => ArtworkModerationStatus::Approved,
            'is_visible' => true,
            'sort_order' => 0,
        ]);

        $setting = FeaturedMakerSetting::query()->create([
            'slot' => FeaturedMakerSetting::POST_LOGIN_SLOT,
            'maker_id' => $maker->id,
            'featured_artwork_id' => $artwork->id,
            'is_active' => true,
            'updated_by' => $admin->id,
        ]);

        $this->assertTrue($setting->maker->is($maker));
        $this->assertTrue($setting->featuredArtwork->is($artwork));
        $this->assertTrue($setting->updatedBy->is($admin));
    }
}
