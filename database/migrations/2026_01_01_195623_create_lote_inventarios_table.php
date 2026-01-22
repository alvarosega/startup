<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Cabecera de Compras
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('provider_id')->constrained('providers');
            $table->foreignId('user_id')->constrained('users'); // Quien registró
            
            $table->string('document_number')->index(); // Nro Factura
            $table->date('purchase_date');
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->string('status')->default('COMPLETED'); // DRAFT, COMPLETED, CANCELLED
            
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Lotes de Inventario (Detalle de Compra + Stock)
        Schema::create('inventory_lots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained('purchases')->onDelete('cascade');
            $table->foreignId('sku_id')->constrained('skus');
            $table->foreignId('branch_id')->constrained('branches');
            
            $table->string('lot_code')->unique(); // Generado autom: LOT-2401-ABCD
            $table->integer('quantity'); // Stock Físico Actual
            $table->integer('initial_quantity'); // Cuánto entró originalmente (Auditoría)
            $table->integer('reserved_quantity')->default(0); // Para ventas en proceso
            
            $table->decimal('unit_cost', 10, 2); // Costo real de compra
            $table->date('expiration_date')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchases');
        Schema::dropIfExists('inventory_lots');
    }
};