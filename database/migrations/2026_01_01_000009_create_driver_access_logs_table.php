<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('driver_access_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('driver_id')->nullable()->constrained('drivers')->nullOnDelete();
            $table->string('phone_attempted', 20)->index();
            $table->string('ip_address', 45)->index();
            $table->text('user_agent')->nullable();
            $table->string('status', 30); // SUCCESS, INVALID_CREDENTIALS, SUSPENDED_ACCOUNT, RATE_LIMITED
            $table->string('failure_reason', 255)->nullable();
            $table->timestamp('created_at')->useCurrent()->index();

            $table->index(['phone_attempted', 'created_at']);
            $table->index(['ip_address', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_access_logs');
    }
};