<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maker_profiles', function (Blueprint $table): void {
            $table->json('current_upcoming_shows')
                ->nullable()
                ->after('show_shows_on_info_page');
        });
    }

    public function down(): void
    {
        Schema::table('maker_profiles', function (Blueprint $table): void {
            $table->dropColumn('current_upcoming_shows');
        });
    }
};
