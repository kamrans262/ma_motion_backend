<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maker_saves', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('appreciator_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('maker_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['appreciator_id', 'maker_id']);
            $table->index(['maker_id', 'created_at']);
            $table->index(['appreciator_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maker_saves');
    }
};
