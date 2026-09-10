<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_content_pages', function (Blueprint $table): void {
            $table->id();
            $table->string('title', 180);
            $table->string('slug', 180)->unique();
            $table->longText('body')->nullable();
            $table->boolean('is_published')->default(false)->index();
            $table->boolean('is_system')->default(false)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_published', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_content_pages');
    }
};
