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
            $table->foreignId('user_id')->constrained('users'); 
            
            $table->string('document_number')->index();
            $table->date('purchase_date');
            
            // --- NUEVOS CAMPOS FINANCIEROS ---
            $table->string('payment_type')->default('CASH'); // CASH (Contado), CREDIT (Crédito)
            $table->date('payment_due_date')->nullable();    // Fecha límite de pago
            // ---------------------------------

            $table->decimal('total_amount', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->string('status')->default('COMPLETED'); 
            
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Lotes de Inventario (Detalle de Compra + Stock)
        Schema::create('inventory_lots', function (Blueprint $table) {
            $table->id();
            
            // Purchase SÍ existe antes, así que podemos usar constrained
            $table->foreignId('purchase_id')->nullable()->constrained('purchases'); 
            
            // --- CAMBIO AQUÍ ---
            // Como 'transfers' NO existe aún, creamos solo la columna.
            // No usamos 'constrained()', usamos 'unsignedBigInteger()'
            $table->unsignedBigInteger('transfer_id')->nullable(); 
    
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('sku_id')->constrained('skus');
            
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
        Schema::dropIfExists('purchases');
        Schema::dropIfExists('inventory_lots');
    }
};