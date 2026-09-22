<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_otp_challenges', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('email', 255);
            $table->string('purpose', 16);
            $table->string('code_hash');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('expires_at');
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('consumed_at')->nullable();
            $table->timestamps();
            $table->index(['email', 'purpose', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_otp_challenges');
    }
};
