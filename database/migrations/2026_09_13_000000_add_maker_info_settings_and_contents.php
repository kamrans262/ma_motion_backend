<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maker_profiles', function (Blueprint $table): void {
            $table->boolean('show_website_on_info_page')->default(true)->after('contact_email');
            $table->boolean('show_email_on_info_page')->default(false)->after('show_website_on_info_page');
            $table->boolean('show_shows_on_info_page')->default(true)->after('show_email_on_info_page');
        });

        Schema::create('maker_profile_contents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('maker_profile_id')->constrained('maker_profiles')->cascadeOnDelete();
            $table->unsignedTinyInteger('slot');
            $table->string('kind', 20);
            $table->string('path', 2048);
            $table->string('mime_type', 120)->nullable();
            $table->text('caption')->nullable();
            $table->timestamps();

            $table->unique(['maker_profile_id', 'slot']);
            $table->index(['maker_profile_id', 'slot']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maker_profile_contents');

        Schema::table('maker_profiles', function (Blueprint $table): void {
            $table->dropColumn([
                'show_website_on_info_page',
                'show_email_on_info_page',
                'show_shows_on_info_page',
            ]);
        });
    }
};
