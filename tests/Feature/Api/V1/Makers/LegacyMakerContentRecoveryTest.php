<?php

namespace Tests\Feature\Api\V1\Makers;

use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class LegacyMakerContentRecoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_recovery_migrates_unassigned_image_and_video_media_into_artwork_slots(): void
    {
        Storage::fake('public');

        $maker = User::factory()->create([
            'role' => UserRole::Maker,
            'status' => UserStatus::Active,
        ]);
        $profile = $maker->makerProfile()->create(['location_text' => 'Chicago, IL']);

        foreach ([2 => 'image', 4 => 'video'] as $slot => $kind) {
            $path = 'maker-profiles/'.$maker->id.'/content/legacy-'.$slot.'.'.($kind === 'video' ? 'mp4' : 'jpg');
            Storage::disk('public')->put($path, 'legacy media bytes');
            DB::table('maker_profile_contents')->insert([
                'maker_profile_id' => $profile->id,
                'slot' => $slot,
                'kind' => $kind,
                'path' => $path,
                'mime_type' => $kind === 'video' ? 'video/mp4' : 'image/jpeg',
                'caption' => 'Legacy content '.$slot,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $migration = require base_path('database/migrations/2026_09_21_000000_recover_legacy_maker_artwork_media.php');
        $migration->up();

        $slots = $profile->artworkSlots()->with('artwork.media')->get()->keyBy('slot');
        $this->assertEqualsCanonicalizing([2, 4], $slots->keys()->all());
        $this->assertSame('image', $slots[2]->artwork->primaryMedia->kind);
        $this->assertSame('video', $slots[4]->artwork->primaryMedia->kind);
        $this->assertSame('pending', $slots[4]->artwork->moderation_status->value);
        $this->assertDatabaseMissing('maker_profile_contents', [
            'maker_profile_id' => $profile->id,
            'slot' => 4,
        ]);

        // Running the recovery again must not duplicate existing artwork.
        $migration->up();
        $this->assertDatabaseCount('artworks', 2);
        $this->assertDatabaseCount('maker_profile_artwork_slots', 2);
    }
}
