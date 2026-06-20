<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('purchases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('branch_id')->constrained('branches');
            $table->foreignUuid('provider_id')->constrained('providers');
            $table->foreignUuid('admin_id')->constrained('admins');
            
            $table->string('document_number', 32);
            $table->date('purchase_date')->index();
            $table->enum('payment_type', ['CASH', 'CREDIT'])->default('CASH');
            // LEY: Columna total_amount eliminada por definición de negocio (sin costos)
            $table->string('status')->default('COMPLETED');
            
            $table->unsignedBigInteger('deleted_epoch')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['document_number', 'deleted_epoch'], 'idx_purchases_doc_unique');
        });
    }

    public function down(): void {
        Schema::dropIfExists('purchases');
    }
};