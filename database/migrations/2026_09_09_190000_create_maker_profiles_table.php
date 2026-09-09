<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maker_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->text('bio')->nullable();
            $table->string('location_text', 180)->nullable()->index();
            $table->string('profile_image_path')->nullable();
            $table->timestamps();
        });

        $now = now();

        DB::table('users')
            ->where('role', 'maker')
            ->select('id')
            ->orderBy('id')
            ->chunkById(500, function ($users) use ($now): void {
                $rows = $users->map(static fn ($user): array => [
                    'user_id' => $user->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all();

                if ($rows !== []) {
                    DB::table('maker_profiles')->insertOrIgnore($rows);
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('maker_profiles');
    }
};
