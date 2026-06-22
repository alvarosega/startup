<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customer_legal_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('customer_id');
            $table->string('document_version', 20)->default('1.0'); 
            $table->string('ip_address', 45);
            $table->text('user_agent');
            $table->json('meta_data')->nullable(); 
            $table->timestamp('accepted_at')->useCurrent();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
        });

        Schema::create('driver_legal_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('driver_id');
            $table->string('document_version', 20)->default('1.0'); 
            $table->string('ip_address', 45);
            $table->text('user_agent');
            $table->json('device_snapshot')->nullable(); 
            $table->timestamp('accepted_at')->useCurrent();

            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_legal_logs');
        Schema::dropIfExists('customer_legal_logs');
    }
};