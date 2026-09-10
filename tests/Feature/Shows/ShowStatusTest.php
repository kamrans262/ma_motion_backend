<?php

namespace Tests\Feature\Shows;

use App\Features\Auth\Enums\UserRole;
use App\Features\Shows\Enums\ShowStatus;
use App\Features\Shows\Models\Show;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ShowStatusTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    public function test_status_is_derived_from_dates_without_a_stale_status_column(): void
    {
        CarbonImmutable::setTestNow('2026-09-10 12:00:00');
        $maker = User::factory()->create(['role'=>UserRole::Maker]);

        $current = Show::query()->create(['maker_id'=>$maker->id,'name'=>'Current','start_date'=>'2026-09-01','end_date'=>'2026-09-10','is_visible'=>true,'sort_order'=>0]);
        $upcoming = Show::query()->create(['maker_id'=>$maker->id,'name'=>'Upcoming','start_date'=>'2026-09-11','end_date'=>'2026-09-20','is_visible'=>true,'sort_order'=>0]);
        $past = Show::query()->create(['maker_id'=>$maker->id,'name'=>'Past','start_date'=>'2026-08-01','end_date'=>'2026-09-09','is_visible'=>true,'sort_order'=>0]);

        $this->assertSame(ShowStatus::Current, $current->status());
        $this->assertSame(ShowStatus::Upcoming, $upcoming->status());
        $this->assertSame(ShowStatus::Past, $past->status());
        $this->assertSame(1, Show::query()->withStatus('current')->count());
        $this->assertSame(1, Show::query()->withStatus('upcoming')->count());
        $this->assertSame(1, Show::query()->withStatus('past')->count());
    }
}
