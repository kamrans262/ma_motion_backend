<?php

namespace Database\Seeders;

use App\Features\Artworks\Models\Artwork;
use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Features\Shows\Models\Show;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Test upcoming exhibitions for up to three existing active Makers who own
 * seeded artwork. Does not create or delete users, and is safe to rerun.
 */
final class MaMotionUpcomingShowDemoSeeder extends Seeder
{
    private const DEMO_ARTWORK_PATH = 'demo-artworks/ma-motion-v1/%';

    private const EXHIBITIONS = [
        [
            'name' => '[Demo] Canvas & Light',
            'description' => 'An upcoming presentation of abstract forms, layered colors and light.',
        ],
        [
            'name' => '[Demo] Material Stories',
            'description' => 'A contemporary exhibition exploring organic shapes and material design.',
        ],
        [
            'name' => '[Demo] Forms of Tomorrow',
            'description' => 'Experiments in geometry, illustration and digital artwork.',
        ],
    ];

    public function run(): void
    {
        $makers = User::query()
            ->where('role', UserRole::Maker->value)
            ->where('status', UserStatus::Active->value)
            ->where(function ($query): void {
                $query->whereDoesntHave('makerProfile')
                    ->orWhereHas('makerProfile', static fn ($profile) => $profile->where('is_hidden', false));
            })
            ->whereHas('artworks', static function ($artwork): void {
                $artwork->where('moderation_status', 'approved')
                    ->where('is_visible', true)
                    ->whereHas('media', static function ($media): void {
                        $media->where('path', 'like', self::DEMO_ARTWORK_PATH);
                    });
            })
            ->orderBy('id')
            ->limit(3)
            ->get();

        $count = 0;

        foreach ($makers as $index => $maker) {
            $details = self::EXHIBITIONS[$index];
            $artwork = Artwork::query()
                ->where('maker_id', $maker->id)
                ->where('moderation_status', 'approved')
                ->where('is_visible', true)
                ->whereHas('media', static function ($media): void {
                    $media->where('path', 'like', self::DEMO_ARTWORK_PATH);
                })
                ->orderBy('sort_order')
                ->firstOrFail();

            DB::transaction(function () use ($maker, $index, $details, $artwork): void {
                $profile = $maker->makerProfile()->firstOrCreate(['user_id' => $maker->id]);

                // Discovery orange dots respect this visibility preference.
                if ($profile->show_shows_on_info_page !== true) {
                    $profile->forceFill(['show_shows_on_info_page' => true])->save();
                }

                $start = today()->addDays(14 + 7 * $index);
                $show = Show::query()->updateOrCreate(
                    ['maker_id' => $maker->id, 'name' => $details['name']],
                    [
                        'description' => $details['description'],
                        'location_id' => $profile->location_id,
                        'location_text' => $profile->location_text ?: 'Demo Gallery',
                        'start_date' => $start->toDateString(),
                        'end_date' => $start->copy()->addDays(3)->toDateString(),
                        'is_visible' => true,
                        'sort_order' => $index,
                    ],
                );

                $show->artworks()->syncWithoutDetaching([$artwork->id]);
            });

            $count++;
        }

        $this->command?->info('Upcoming demo shows: '.$count.' visible exhibitions for '.$count.' distinct Makers; orange dot enabled.');
        if ($count < 2) {
            $this->command?->warn('There are fewer than two active Makers with demo artwork; no user accounts were created.');
        }
    }
}
