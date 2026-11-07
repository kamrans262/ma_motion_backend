<?php

namespace Tests\Feature\Api\V1\Artworks;

use App\Features\Admin\Artworks\Actions\ModerateArtworkAction;
use App\Features\Artworks\Models\Artwork;
use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class OneTimeMakerArtworkApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_first_admin_approval_unlocks_future_uploads_and_edits_for_that_maker_only(): void
    {
        Storage::fake('public');
        $maker = User::factory()->create(['role' => UserRole::Maker, 'status' => UserStatus::Active]);
        $token = $maker->createToken('mobile', ['mobile'])->plainTextToken;

        $first = $this->withToken($token)->post('/api/v1/me/artworks', [
            'title' => 'First artwork',
            'media' => [UploadedFile::fake()->image('first.jpg')],
        ])->assertCreated()->assertJsonPath('data.moderation_status', 'pending');
        $firstId = (int) $first->json('data.id');

        $second = $this->withToken($token)->post('/api/v1/me/artworks', [
            'title' => 'Second artwork before approval',
            'media' => [UploadedFile::fake()->image('second.jpg')],
        ])->assertCreated()->assertJsonPath('data.moderation_status', 'pending');

        app(ModerateArtworkAction::class)->execute(
            Artwork::query()->findOrFail($firstId),
            ['moderation_status' => 'approved'],
        );

        $this->assertNotNull($maker->refresh()->makerProfile?->artwork_approval_granted_at);

        // Previously pending items remain queued until separately reviewed.
        $this->assertSame('pending', Artwork::query()->findOrFail($second->json('data.id'))->moderation_status->value);

        $third = $this->withToken($token)->post('/api/v1/me/artworks', [
            'title' => 'Third artwork after approval',
            'media' => [UploadedFile::fake()->image('third.jpg')],
        ])->assertCreated()
            ->assertJsonPath('data.moderation_status', 'approved')
            ->assertJsonPath('message', 'Artwork uploaded successfully.');
        $thirdId = (int) $third->json('data.id');

        $this->withToken($token)->patchJson('/api/v1/me/artworks/'.$thirdId, [
            'title' => 'Edited third artwork',
        ])->assertOk()->assertJsonPath('data.moderation_status', 'approved');

        $added = $this->withToken($token)->post('/api/v1/me/artworks/'.$thirdId.'/media', [
            'media' => [UploadedFile::fake()->image('another.jpg')],
        ])->assertCreated()->assertJsonPath('data.moderation_status', 'approved');

        $firstMediaId = (int) $added->json('data.media.0.id');
        $this->withToken($token)
            ->deleteJson('/api/v1/me/artworks/'.$thirdId.'/media/'.$firstMediaId)
            ->assertOk()->assertJsonPath('data.moderation_status', 'approved');

        // Grant is durable even if the first approved artwork is removed.
        $this->withToken($token)->deleteJson('/api/v1/me/artworks/'.$firstId)->assertOk();
        $this->withToken($token)->post('/api/v1/me/artworks', [
            'title' => 'Fourth artwork',
            'media' => [UploadedFile::fake()->image('fourth.jpg')],
        ])->assertCreated()->assertJsonPath('data.moderation_status', 'approved');

        $anotherMaker = User::factory()->create(['role' => UserRole::Maker, 'status' => UserStatus::Active]);
        $otherToken = $anotherMaker->createToken('mobile', ['mobile'])->plainTextToken;
        $this->withToken($otherToken)->post('/api/v1/me/artworks', [
            'title' => 'Another Makers first upload',
            'media' => [UploadedFile::fake()->image('different.jpg')],
        ])->assertCreated()->assertJsonPath('data.moderation_status', 'pending');
    }

    public function test_rejected_artwork_requires_review_when_resubmitted_even_after_maker_approval(): void
    {
        Storage::fake('public');
        $maker = User::factory()->create(['role' => UserRole::Maker, 'status' => UserStatus::Active]);
        $maker->makerProfile()->create()->forceFill(['artwork_approval_granted_at' => now()])->save();
        $artwork = Artwork::query()->create([
            'maker_id' => $maker->id,
            'title' => 'Rejected item',
            'moderation_status' => 'rejected',
            'rejection_reason' => 'Needs changes',
            'is_visible' => true,
            'sort_order' => 0,
        ]);
        $token = $maker->createToken('mobile', ['mobile'])->plainTextToken;

        $this->withToken($token)->patchJson('/api/v1/me/artworks/'.$artwork->id, [
            'title' => 'Reworked item',
        ])->assertOk()->assertJsonPath('data.moderation_status', 'pending')
            ->assertJsonPath('data.rejection_reason', null);
    }
}
