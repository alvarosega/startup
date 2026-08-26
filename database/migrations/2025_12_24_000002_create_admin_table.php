<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone', 20)->nullable();
            $table->uuid('branch_id')->nullable()->index();
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('is_active')->default(false);
            $table->string('mfa_secret')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();

            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
        });
        Schema::create('admin_access_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('email_attempted')->index();
            $table->string('ip_address', 45);
            $table->text('user_agent')->nullable();
            $table->string('status', 30); // success, failed_credentials, failed_inactive, failed_role, blocked_rate_limit
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_access_logs');
        Schema::dropIfExists('admins');
    }
};