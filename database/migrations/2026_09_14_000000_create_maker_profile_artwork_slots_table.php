<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maker_profile_artwork_slots', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('maker_profile_id')->constrained('maker_profiles')->cascadeOnDelete();
            $table->unsignedTinyInteger('slot');
            $table->foreignId('artwork_id')->constrained('artworks')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['maker_profile_id', 'slot']);
            $table->unique(['maker_profile_id', 'artwork_id']);
            $table->index(['artwork_id', 'slot']);
        });

        $this->backfillLegacyArtworkImages();
    }

    private function backfillLegacyArtworkImages(): void
    {
        DB::table('maker_profile_contents')
            ->whereIn('slot', [2, 3])
            ->where('kind', 'image')
            ->orderBy('id')
            ->each(function (object $content): void {
                $profile = DB::table('maker_profiles')->where('id', $content->maker_profile_id)->first();

                if ($profile === null) {
                    return;
                }

                $typeId = DB::table('maker_profile_type')
                    ->where('maker_profile_id', $profile->id)
                    ->orderBy('artwork_type_id')
                    ->value('artwork_type_id');

                $styleId = DB::table('maker_profile_style')
                    ->where('maker_profile_id', $profile->id)
                    ->orderBy('artwork_style_id')
                    ->value('artwork_style_id');

                $caption = trim((string) ($content->caption ?? ''));
                $title = $caption !== ''
                    ? Str::limit($caption, 180, '')
                    : 'Untitled artwork '.((int) $content->slot - 1);

                $artworkId = DB::table('artworks')->insertGetId([
                    'maker_id' => $profile->user_id,
                    'artwork_type_id' => $typeId,
                    'artwork_style_id' => $styleId,
                    'location_id' => $profile->location_id,
                    'title' => $title,
                    'description' => $caption !== '' ? $caption : null,
                    'location_text' => $profile->location_text,
                    'moderation_status' => 'pending',
                    'is_visible' => true,
                    'rejection_reason' => null,
                    'sort_order' => max(((int) $content->slot) - 2, 0),
                    'created_at' => $content->created_at,
                    'updated_at' => $content->updated_at,
                    'deleted_at' => null,
                ]);

                DB::table('artwork_media')->insert([
                    'artwork_id' => $artworkId,
                    'kind' => 'image',
                    'disk' => 'public',
                    'path' => $content->path,
                    'mime_type' => $content->mime_type ?: 'image/jpeg',
                    'size_bytes' => 0,
                    'width' => null,
                    'height' => null,
                    'alt_text' => $title,
                    'sort_order' => 0,
                    'is_primary' => true,
                    'created_at' => $content->created_at,
                    'updated_at' => $content->updated_at,
                ]);

                DB::table('maker_profile_artwork_slots')->insert([
                    'maker_profile_id' => $profile->id,
                    'slot' => $content->slot,
                    'artwork_id' => $artworkId,
                    'created_at' => $content->created_at,
                    'updated_at' => $content->updated_at,
                ]);

                DB::table('maker_profile_contents')->where('id', $content->id)->delete();
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('maker_profile_artwork_slots');
    }
};
