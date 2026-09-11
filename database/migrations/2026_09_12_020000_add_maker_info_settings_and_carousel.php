<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maker_profiles', function (Blueprint $table): void {
            $table->boolean('show_website_on_info_page')->default(true);
            $table->boolean('show_email_on_info_page')->default(false);
            $table->boolean('show_shows_on_info_page')->default(true);
        });

        Schema::create('maker_profile_carousel_media', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('maker_profile_id')->constrained('maker_profiles')->cascadeOnDelete();
            $table->unsignedTinyInteger('slot');
            $table->string('kind', 20);
            $table->string('disk', 40)->default('public');
            $table->string('path', 2048);
            $table->string('mime_type', 120)->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->string('caption', 280)->nullable();
            $table->timestamps();

            $table->unique(['maker_profile_id', 'slot'], 'maker_profile_carousel_slot_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maker_profile_carousel_media');

        Schema::table('maker_profiles', function (Blueprint $table): void {
            $table->dropColumn([
                'show_website_on_info_page',
                'show_email_on_info_page',
                'show_shows_on_info_page',
            ]);
        });
    }
};
