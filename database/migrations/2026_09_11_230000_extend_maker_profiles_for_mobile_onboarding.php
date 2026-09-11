<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maker_profiles', function (Blueprint $table): void {
            $table->string('website_url', 2048)->nullable()->after('profile_image_path');
            $table->string('contact_email')->nullable()->after('website_url');
            $table->timestamp('onboarding_completed_at')->nullable()->after('contact_email');
        });

        Schema::create('maker_profile_type', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('maker_profile_id')->constrained('maker_profiles')->cascadeOnDelete();
            $table->foreignId('artwork_type_id')->constrained('artwork_types')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['maker_profile_id', 'artwork_type_id'], 'maker_profile_type_unique');
        });

        Schema::create('maker_profile_style', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('maker_profile_id')->constrained('maker_profiles')->cascadeOnDelete();
            $table->foreignId('artwork_style_id')->constrained('artwork_styles')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['maker_profile_id', 'artwork_style_id'], 'maker_profile_style_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maker_profile_style');
        Schema::dropIfExists('maker_profile_type');

        Schema::table('maker_profiles', function (Blueprint $table): void {
            $table->dropColumn(['website_url', 'contact_email', 'onboarding_completed_at']);
        });
    }
};
