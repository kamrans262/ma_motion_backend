<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shows', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('maker_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->string('name', 180);
            $table->text('description')->nullable();
            $table->string('location_text', 180)->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_visible')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['maker_id', 'start_date']);
            $table->index(['maker_id', 'end_date']);
            $table->index(['location_id', 'start_date']);
            $table->index(['is_visible', 'start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shows');
    }
};
