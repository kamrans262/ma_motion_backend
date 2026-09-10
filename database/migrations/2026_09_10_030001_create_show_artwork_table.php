<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('show_artwork', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('show_id')->constrained('shows')->cascadeOnDelete();
            $table->foreignId('artwork_id')->constrained('artworks')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['show_id', 'artwork_id']);
            $table->index(['show_id', 'sort_order']);
            $table->index(['artwork_id', 'show_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('show_artwork');
    }
};
