<?php

namespace Tests\Feature\Admin;

use App\Features\Artworks\Enums\ArtworkModerationStatus;
use App\Features\Artworks\Models\Artwork;
use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Features\Saves\Models\MakerSave;
use App\Features\Shows\Models\Show;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AdminAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    public function test_admin_analytics_uses_live_platform_data_and_derived_show_statuses(): void
    {
        CarbonImmutable::setTestNow('2026-09-10 12:00:00');
        $admin = User::factory()->create(['role' => UserRole::Admin, 'status' => UserStatus::Active]);
        $maker = User::factory()->create(['name' => 'Analytics Popular Maker', 'role' => UserRole::Maker, 'status' => UserStatus::Active]);
        $appreciator = User::factory()->create(['role' => UserRole::Appreciator, 'status' => UserStatus::Active]);
        MakerSave::query()->create(['appreciator_id' => $appreciator->id, 'maker_id' => $maker->id]);
        Artwork::query()->create(['maker_id' => $maker->id, 'title' => 'Analytics Artwork', 'moderation_status' => ArtworkModerationStatus::Approved->value, 'is_visible' => true]);
        Show::query()->create(['maker_id' => $maker->id, 'name' => 'Current Analytics Show', 'start_date' => '2026-09-01', 'end_date' => '2026-09-10']);
        Show::query()->create(['maker_id' => $maker->id, 'name' => 'Upcoming Analytics Show', 'start_date' => '2026-09-11', 'end_date' => '2026-09-20']);
        Show::query()->create(['maker_id' => $maker->id, 'name' => 'Past Analytics Show', 'start_date' => '2026-08-01', 'end_date' => '2026-09-09']);

        $this->actingAs($admin)->get('/admin/analytics?period=30')
            ->assertOk()
            ->assertSee('Analytics')
            ->assertSee('Total Users')
            ->assertSee('Current Shows')
            ->assertSee('Upcoming Shows')
            ->assertSee('Past Shows')
            ->assertSee('Analytics Popular Maker')
            ->assertSee('ma-analytics-grid', false);
    }

    public function test_admin_can_export_analytics_csv(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'status' => UserStatus::Active]);
        $response = $this->actingAs($admin)->get('/admin/analytics/export?period=7');
        $response->assertOk();
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('content-type'));
        $this->assertStringContainsString('attachment', (string) $response->headers->get('content-disposition'));
    }
}
