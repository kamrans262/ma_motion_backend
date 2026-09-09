<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artworks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('maker_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('artwork_type_id')->nullable()->constrained('artwork_types')->nullOnDelete();
            $table->foreignId('artwork_style_id')->nullable()->constrained('artwork_styles')->nullOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->string('title', 180);
            $table->text('description')->nullable();
            $table->string('location_text', 180)->nullable();
            $table->string('moderation_status', 20)->default('pending');
            $table->boolean('is_visible')->default(true);
            $table->string('rejection_reason', 1000)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['maker_id', 'sort_order']);
            $table->index(['maker_id', 'created_at']);
            $table->index(['moderation_status', 'is_visible']);
            $table->index(['artwork_type_id', 'artwork_style_id']);
            $table->index(['location_id', 'moderation_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artworks');
    }
};
