<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artwork_saves', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('artwork_id')->constrained('artworks')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'artwork_id']);
            $table->index(['user_id', 'created_at']);
            $table->index(['artwork_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artwork_saves');
    }
};
