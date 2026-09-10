<?php

namespace Tests\Feature\Admin;

use App\Features\Artworks\Enums\ArtworkModerationStatus;
use App\Features\Artworks\Models\Artwork;
use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Features\FeaturedMaker\Models\FeaturedMakerSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminFeaturedMakerManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_select_update_publish_and_remove_featured_maker(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'status' => UserStatus::Active]);
        $maker = User::factory()->create(['name' => 'Spotlight Maker', 'role' => UserRole::Maker, 'status' => UserStatus::Active]);
        $artwork = Artwork::query()->create([
            'maker_id' => $maker->id,
            'title' => 'Hero Artwork',
            'moderation_status' => ArtworkModerationStatus::Approved,
            'is_visible' => true,
            'sort_order' => 0,
        ]);

        $this->actingAs($admin)->put('/admin/featured-maker', [
            'maker_id' => $maker->id,
            'featured_artwork_id' => $artwork->id,
            'eyebrow' => 'Featured Maker',
            'headline' => 'A Maker in Motion',
            'description' => 'Curated spotlight content.',
            'is_active' => '1',
        ])->assertRedirect(route('admin.featured-maker.index'));

        $this->assertDatabaseHas('featured_maker_settings', [
            'slot' => FeaturedMakerSetting::POST_LOGIN_SLOT,
            'maker_id' => $maker->id,
            'featured_artwork_id' => $artwork->id,
            'is_active' => 1,
            'updated_by' => $admin->id,
        ]);

        $this->actingAs($admin)->get('/admin/featured-maker')
            ->assertOk()
            ->assertSee('Featured Maker')
            ->assertSee('A Maker in Motion')
            ->assertSee('Hero Artwork');

        $this->actingAs($admin)->delete('/admin/featured-maker')
            ->assertRedirect(route('admin.featured-maker.index'));

        $this->assertDatabaseMissing('featured_maker_settings', [
            'slot' => FeaturedMakerSetting::POST_LOGIN_SLOT,
        ]);
    }

    public function test_admin_cannot_feature_artwork_owned_by_another_maker(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'status' => UserStatus::Active]);
        $maker = User::factory()->create(['role' => UserRole::Maker, 'status' => UserStatus::Active]);
        $otherMaker = User::factory()->create(['role' => UserRole::Maker, 'status' => UserStatus::Active]);
        $otherArtwork = Artwork::query()->create([
            'maker_id' => $otherMaker->id,
            'title' => 'Other Maker Artwork',
            'moderation_status' => ArtworkModerationStatus::Approved,
            'is_visible' => true,
            'sort_order' => 0,
        ]);

        $this->actingAs($admin)->from('/admin/featured-maker')->put('/admin/featured-maker', [
            'maker_id' => $maker->id,
            'featured_artwork_id' => $otherArtwork->id,
            'is_active' => '1',
        ])->assertRedirect('/admin/featured-maker')->assertSessionHasErrors('featured_artwork_id');
    }

    public function test_admin_can_only_select_active_maker_accounts(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'status' => UserStatus::Active]);
        $inactiveMaker = User::factory()->create(['role' => UserRole::Maker, 'status' => UserStatus::Inactive]);

        $this->actingAs($admin)->from('/admin/featured-maker')->put('/admin/featured-maker', [
            'maker_id' => $inactiveMaker->id,
            'is_active' => '1',
        ])->assertRedirect('/admin/featured-maker')->assertSessionHasErrors('maker_id');
    }
}
