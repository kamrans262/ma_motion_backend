<?php

namespace Tests\Feature\Api\V1\Artworks;

use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Tests\TestCase;

final class FiveSecondArtworkVideoApiTest extends TestCase
{
    use RefreshDatabase;

    /** @var list<string> */
    private array $clipPaths = [];

    protected function tearDown(): void
    {
        foreach ($this->clipPaths as $path) {
            @unlink($path);
        }

        parent::tearDown();
    }

    public function test_valid_short_artwork_video_can_be_created_added_and_assigned_to_content_two(): void
    {
        $this->requireMediaTools();
        Storage::fake('public');

        $maker = User::factory()->create([
            'role' => UserRole::Maker,
            'status' => UserStatus::Active,
        ]);
        $token = $maker->createToken('mobile', ['mobile'])->plainTextToken;
        $headers = ['Accept' => 'application/json'];

        $created = $this->withToken($token)
            ->post('/api/v1/me/artworks', [
                'title' => 'Five-second video artwork',
                'media' => [$this->clip(4)],
            ], $headers)
            ->assertCreated()
            ->assertJsonPath('data.media.0.kind', 'video')
            ->assertJsonPath('data.media.0.mime_type', 'video/mp4');

        $id = (int) $created->json('data.id');
        $this->assertNotEmpty($created->json('data.media.0.url'));

        $this->withToken($token)
            ->post('/api/v1/me/artworks/'.$id.'/media', [
                'media' => [$this->clip(5)],
            ], $headers)
            ->assertCreated()
            ->assertJsonPath('data.media.1.kind', 'video');

        $this->withToken($token)
            ->putJson('/api/v1/me/maker-profile/artwork-slots/2', [
                'artwork_id' => $id,
            ])
            ->assertOk()
            ->assertJsonPath('data.artwork.media.0.kind', 'video');

        $this->assertDatabaseHas('artwork_media', [
            'artwork_id' => $id,
            'kind' => 'video',
            'mime_type' => 'video/mp4',
        ]);
    }

    public function test_videos_longer_than_five_seconds_are_rejected_for_artworks_and_content_one(): void
    {
        $this->requireMediaTools();
        Storage::fake('public');

        $maker = User::factory()->create([
            'role' => UserRole::Maker,
            'status' => UserStatus::Active,
        ]);
        $token = $maker->createToken('mobile', ['mobile'])->plainTextToken;
        $headers = ['Accept' => 'application/json'];

        $this->withToken($token)
            ->post('/api/v1/me/artworks', [
                'title' => 'Too long',
                'media' => [$this->clip(6)],
            ], $headers)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('media');

        $this->assertDatabaseCount('artworks', 0);
        $this->assertDatabaseCount('artwork_media', 0);

        $this->withToken($token)
            ->post('/api/v1/me/maker-profile/carousel/1', [
                'media' => $this->clip(6),
            ], $headers)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('media');

        $this->withToken($token)
            ->post('/api/v1/me/maker-profile/carousel/1', [
                'media' => $this->clip(5),
            ], $headers)
            ->assertOk()
            ->assertJsonPath('data.kind', 'video');
    }

    private function requireMediaTools(): void
    {
        foreach (['ffmpeg', (string) config('media.ffprobe_binary')] as $binary) {
            try {
                $process = new Process([$binary, '-version']);
                $process->run();

                if (! $process->isSuccessful()) {
                    $this->markTestSkipped($binary.' is required for the video integration test.');
                }
            } catch (\Throwable) {
                $this->markTestSkipped($binary.' is not installed.');
            }
        }
    }

    private function clip(int $seconds): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'ma_video_');
        if ($path === false) {
            throw new \RuntimeException('Could not prepare video fixture.');
        }

        $this->clipPaths[] = $path;
        $process = new Process([
            'ffmpeg', '-hide_banner', '-loglevel', 'error',
            '-f', 'lavfi', '-i', 'color=c=black:s=16x16:r=2',
            '-t', (string) $seconds, '-an', '-c:v', 'mpeg4',
            '-q:v', '12', '-f', 'mp4', '-y', $path,
        ]);
        $process->setTimeout(20);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new \RuntimeException('Could not create video fixture: '.$process->getErrorOutput());
        }

        return new UploadedFile($path, 'artwork.mp4', 'video/mp4', null, true);
    }
}
