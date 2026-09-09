<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artwork_media', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('artwork_id')->constrained('artworks')->cascadeOnDelete();
            $table->string('kind', 20)->default('image');
            $table->string('disk', 40)->default('public');
            $table->string('path', 500)->unique();
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size_bytes');
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('alt_text', 180)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->index(['artwork_id', 'sort_order']);
            $table->index(['artwork_id', 'is_primary']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artwork_media');
    }
};
