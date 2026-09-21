<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // The original artwork-slot migration only migrated images in slots 2
        // and 3. Recover any remaining legacy images/videos in slots 2–4
        // without replacing a Maker's newer, already assigned artwork.
        DB::table('maker_profile_contents')
            ->whereIn('slot', [2, 3, 4])
            ->orderBy('id')
            ->get()
            ->each(function (object $content): void {
                DB::transaction(function () use ($content): void {
                    $profile = DB::table('maker_profiles')
                        ->where('id', $content->maker_profile_id)->first();

                    if ($profile === null ||
                        DB::table('maker_profile_artwork_slots')
                            ->where('maker_profile_id', $profile->id)
                            ->where('slot', $content->slot)->exists()) {
                        return;
                    }

                    // Preserve the original record for investigation if its
                    // referenced file is missing instead of creating dead links.
                    if (! is_string($content->path) ||
                        ! Storage::disk('public')->exists($content->path)) {
                        return;
                    }

                    $isVideo = $content->kind === 'video' ||
                        str_starts_with((string) $content->mime_type, 'video/');
                    $typeId = DB::table('maker_profile_type')
                        ->where('maker_profile_id', $profile->id)
                        ->orderBy('artwork_type_id')->value('artwork_type_id');
                    $styleId = DB::table('maker_profile_style')
                        ->where('maker_profile_id', $profile->id)
                        ->orderBy('artwork_style_id')->value('artwork_style_id');
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
                        'sort_order' => max((int) $content->slot - 2, 0),
                        'created_at' => $content->created_at,
                        'updated_at' => $content->updated_at,
                        'deleted_at' => null,
                    ]);

                    DB::table('artwork_media')->insert([
                        'artwork_id' => $artworkId,
                        'kind' => $isVideo ? 'video' : 'image',
                        'disk' => 'public',
                        'path' => $content->path,
                        'mime_type' => $content->mime_type,
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
            });
    }

    public function down(): void
    {
        // Intentionally no destructive rollback: this migration transfers
        // ownership of existing media to canonical Artwork records.
    }
};
