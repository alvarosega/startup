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
            $table->uuid('id')->primary(); // UUID
            $table->foreignId('branch_id')->constrained('branches'); // Branches sigue siendo INT
            
            $table->foreignUuid('provider_id')->constrained('providers');
            $table->foreignUuid('user_id')->constrained('users');
            
            $table->string('document_number')->index();
            $table->date('purchase_date');
            
            $table->string('payment_type')->default('CASH'); 
            $table->date('payment_due_date')->nullable(); 

            $table->decimal('total_amount', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->string('status')->default('COMPLETED'); 
            
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Lotes de Inventario (Detalle de Compra + Stock)
        Schema::create('inventory_lots', function (Blueprint $table) {
            $table->id(); // El ID interno del lote puede ser INT, no afecta relaciones externas
            
            // CORRECCIÓN 1: Purchase es UUID
            $table->foreignUuid('purchase_id')->nullable()->constrained('purchases'); 
            
            // Placeholder para transferencias
            $table->unsignedBigInteger('transfer_id')->nullable(); 
    
            $table->foreignId('branch_id')->constrained('branches');
            
            // CORRECCIÓN 2: SKU ahora es UUID (lo cambiamos en el paso anterior)
            $table->foreignUuid('sku_id')->constrained('skus');
            
            $table->string('lot_code')->unique();
            $table->integer('quantity');
            $table->integer('initial_quantity');
            $table->integer('reserved_quantity')->default(0);
            
            $table->decimal('unit_cost', 10, 2);
            $table->date('expiration_date')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_lots');
        Schema::dropIfExists('purchases');
    }
};