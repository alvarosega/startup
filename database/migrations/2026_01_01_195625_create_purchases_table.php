<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('purchases', function (Blueprint $table) {
            $table->uuid('id')->primary(); // Generación secuencial UUIDv7 en App
            $table->foreignUuid('branch_id')->constrained('branches')->restrictOnDelete();
            $table->foreignUuid('provider_id')->constrained('providers')->restrictOnDelete();
            $table->foreignUuid('admin_id')->constrained('admins')->restrictOnDelete();
            
            $table->string('document_number', 32);
            $table->date('purchase_date')->index();
            $table->enum('payment_type', ['CASH', 'CREDIT'])->default('CASH');
            $table->string('status')->default('COMPLETED')->index();
            
            $table->unsignedBigInteger('deleted_epoch')->default(0);
            $table->timestamps();
            $table->softDeletes();

            // Clave única compuesta para evitar colisión de documentos pero permitir re-ingreso si se borra lógicamente
            $table->unique(['document_number', 'deleted_epoch'], 'idx_purchases_doc_unique');
        });
    }

    public function down(): void {
        Schema::dropIfExists('purchases');
    }
};