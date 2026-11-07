<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maker_profiles', function (Blueprint $table): void {
            $table->timestamp('artwork_approval_granted_at')->nullable();
        });

        // Makers with existing approved artworks are already approved.
        // Include soft-deleted artworks, so a past approval remains valid.
        DB::table('maker_profiles')
            ->whereIn('user_id', DB::table('artworks')
                ->where('moderation_status', 'approved')
                ->select('maker_id'))
            ->update(['artwork_approval_granted_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('maker_profiles', function (Blueprint $table): void {
            $table->dropColumn('artwork_approval_granted_at');
        });
    }
};
