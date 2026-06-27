<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('purchases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('branch_id')->constrained('branches')->restrictOnDelete();
            $table->foreignUuid('provider_id')->constrained('providers')->restrictOnDelete();
            $table->foreignUuid('admin_id')->constrained('admins')->restrictOnDelete();
            
            $table->string('document_number', 32);
            $table->date('purchase_date')->index();
            $table->enum('payment_type', ['CASH', 'CREDIT'])->default('CASH');
            
            // RECTIFICACIÓN: Catálogo cerrado de estados para evitar ambigüedades operacionales
            $table->enum('status', ['PENDING', 'COMPLETED', 'CANCELLED'])->default('PENDING')->index();
            
            $table->unsignedBigInteger('deleted_epoch')->default(0);
            $table->timestamps();
            $table->softDeletes(); // RECTIFICACIÓN: Blindaje contra eliminación física cruda

            $table->unique(['provider_id', 'document_number', 'deleted_epoch'], 'idx_purchases_doc_unique');
        });
    }

    public function down(): void {
        Schema::dropIfExists('purchases');
    }
};