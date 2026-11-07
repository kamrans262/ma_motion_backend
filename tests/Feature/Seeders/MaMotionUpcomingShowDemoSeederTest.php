<?php

namespace Tests\Feature\Seeders;

use App\Features\Artworks\Models\Artwork;
use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Features\Shows\Models\Show;
use App\Models\User;
use Database\Seeders\MaMotionUpcomingShowDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class MaMotionUpcomingShowDemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_three_existing_demo_makers_receive_visible_upcoming_shows(): void
    {
        $originalArtworks = [];

        for ($index = 0; $index < 3; $index++) {
            $maker = User::factory()->create([
                'role' => UserRole::Maker,
                'status' => UserStatus::Active,
            ]);
            $maker->makerProfile()->create(['show_shows_on_info_page' => false]);
            $artwork = Artwork::query()->create([
                'maker_id' => $maker->id,
                'title' => 'Demo art '.$index,
                'moderation_status' => 'approved',
                'is_visible' => true,
            ]);
            $artwork->media()->create([
                'kind' => 'image',
                'disk' => 'public',
                'path' => 'demo-artworks/ma-motion-v1/test-'.$index.'.png',
                'mime_type' => 'image/png',
                'size_bytes' => 1024,
                'is_primary' => true,
            ]);
            $originalArtworks[] = [$maker, $artwork];
        }

        $userCount = User::query()->count();
        $this->seed(MaMotionUpcomingShowDemoSeeder::class);

        $this->assertSame(3, Show::query()->count());
        $this->assertSame($userCount, User::query()->count());

        foreach ($originalArtworks as [$maker, $artwork]) {
            $show = Show::query()->where('maker_id', $maker->id)->firstOrFail();
            $this->assertTrue($show->start_date->isFuture());
            $this->assertTrue($show->is_visible);
            $this->assertTrue($maker->refresh()->makerProfile->show_shows_on_info_page);
            $this->assertTrue($show->artworks()->whereKey($artwork->id)->exists());
        }

        $this->getJson('/api/v1/discovery/artworks')
            ->assertOk()->assertJsonFragment(['has_current_or_upcoming_show' => true]);

        $this->seed(MaMotionUpcomingShowDemoSeeder::class);
        $this->assertSame(3, Show::query()->count());
    }

    public function test_without_demo_artworks_no_real_account_is_modified(): void
    {
        $maker = User::factory()->create([
            'role' => UserRole::Maker,
            'status' => UserStatus::Active,
        ]);

        $this->seed(MaMotionUpcomingShowDemoSeeder::class);

        $this->assertTrue(User::query()->whereKey($maker->id)->exists());
        $this->assertSame(0, Show::query()->count());
    }
}
