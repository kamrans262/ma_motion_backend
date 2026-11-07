<?php

namespace Tests\Feature\Seeders;

use App\Features\Artworks\Models\Artwork;
use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Models\User;
use Database\Seeders\MaMotionArtworkDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class MaMotionArtworkDemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seeding_preserves_users_archives_missing_artworks_and_generates_real_png_files(): void
    {
        Storage::fake('public');
        $maker = User::factory()->create(['role' => UserRole::Maker, 'status' => UserStatus::Active]);
        $appreciator = User::factory()->create(['role' => UserRole::Appreciator, 'status' => UserStatus::Active]);
        $broken = Artwork::query()->create([
            'maker_id' => $maker->id, 'title' => 'Broken imported demo',
            'moderation_status' => 'pending', 'is_visible' => true,
        ]);
        $broken->media()->create([
            'kind' => 'image', 'disk' => 'public', 'path' => 'artworks/missing-image.jpg',
            'mime_type' => 'image/jpeg', 'size_bytes' => 0, 'is_primary' => true,
        ]);

        $this->seed(MaMotionArtworkDemoSeeder::class);

        $this->assertSame(2, User::query()->count());
        $this->assertTrue(User::query()->whereKey($appreciator->id)->exists());
        $this->assertSoftDeleted('artworks', ['id' => $broken->id]);
        $this->assertSame(12, Artwork::query()->where('moderation_status', 'approved')->count());

        $artwork = Artwork::query()->with('primaryMedia')->firstOrFail();
        $path = $artwork->primaryMedia->path;
        Storage::disk('public')->assertExists($path);
        $contents = Storage::disk('public')->get($path);
        $this->assertSame("\x89PNG\r\n\x1a\n", substr($contents, 0, 8));
        $dimensions = getimagesizefromstring($contents);
        $this->assertSame(640, $dimensions[0]);
        $this->assertSame(800, $dimensions[1]);
        $this->assertNotEmpty($artwork->description);
        $this->assertNotNull($maker->refresh()->makerProfile?->artwork_approval_granted_at);

        $this->seed(MaMotionArtworkDemoSeeder::class);
        $this->assertSame(12, Artwork::query()->count());
        $this->assertSame(2, User::query()->count());
    }

    public function test_existing_valid_artwork_is_preserved(): void
    {
        Storage::fake('public');
        $maker = User::factory()->create(['role' => UserRole::Maker, 'status' => UserStatus::Active]);
        Storage::disk('public')->put('artworks/keep.jpg', 'not empty');
        $original = Artwork::query()->create([
            'maker_id' => $maker->id, 'title' => 'Original work',
            'moderation_status' => 'approved', 'is_visible' => true,
        ]);
        $original->media()->create([
            'kind' => 'image', 'disk' => 'public', 'path' => 'artworks/keep.jpg',
            'mime_type' => 'image/jpeg', 'size_bytes' => 9, 'is_primary' => true,
        ]);

        $this->seed(MaMotionArtworkDemoSeeder::class);
        $this->assertDatabaseHas('artworks', ['id' => $original->id, 'deleted_at' => null, 'title' => 'Original work']);
        Storage::disk('public')->assertExists('artworks/keep.jpg');
    }
}
