<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('featured_maker_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('slot', 40)->unique();
            $table->foreignId('maker_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('featured_artwork_id')->nullable()->constrained('artworks')->nullOnDelete();
            $table->string('eyebrow', 80)->nullable();
            $table->string('headline', 180)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(false);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['is_active', 'maker_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('featured_maker_settings');
    }
};
