<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customer_loyalty_ledger', function (Blueprint $table) {
            $table->id();
            $table->uuid('customer_id');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            
            $table->integer('amount'); 
            $table->string('type'); 
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            
            $table->index(['customer_id', 'created_at']);
        });

        Schema::create('customer_verifications', function (Blueprint $table) {
            $table->id();
            $table->uuid('customer_id');
            $table->uuid('reviewer_id')->nullable();
            $table->unsignedBigInteger('rejection_reason_id')->nullable(); // Corregido tipado y nomenclatura singular
            
            $table->string('front_ci_path')->nullable();
            $table->string('back_ci_path')->nullable();
            $table->string('selfie_path')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('reviewer_id')->references('id')->on('admins')->nullOnDelete();
            $table->foreign('rejection_reason_id')->references('id')->on('rejection_reasons')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_verifications');
        Schema::dropIfExists('customer_loyalty_ledger');
    }
};