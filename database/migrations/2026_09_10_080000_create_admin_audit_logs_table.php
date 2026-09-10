<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('admin_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event', 191);
            $table->string('route_name', 191)->nullable();
            $table->string('method', 10);
            $table->string('path', 500);
            $table->string('target_type', 80)->nullable();
            $table->string('target_id', 191)->nullable();
            $table->json('request_data')->nullable();
            $table->unsignedSmallInteger('response_status');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 1000)->nullable();
            $table->timestamps();

            $table->index(['admin_user_id', 'created_at']);
            $table->index(['event', 'created_at']);
            $table->index(['route_name', 'created_at']);
            $table->index(['response_status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_audit_logs');
    }
};
