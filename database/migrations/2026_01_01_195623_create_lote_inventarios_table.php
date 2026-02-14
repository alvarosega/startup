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
            $table->char('id', 16)->charset('binary')->primary(); // <--- Binario
            
            $table->char('branch_id', 16)->charset('binary');
            $table->foreign('branch_id')->references('id')->on('branches'); 
            
            $table->char('provider_id', 16)->charset('binary'); // <--- Binario
            $table->foreign('provider_id')->references('id')->on('providers'); 
            
            $table->char('admin_id', 16)->charset('binary');
            $table->foreign('admin_id')->references('id')->on('admins');
            
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

        // 2. Lotes de Inventario
        Schema::create('inventory_lots', function (Blueprint $table) {
            $table->char('id', 16)->charset('binary')->primary(); // <--- Binario
            
            $table->char('purchase_id', 16)->charset('binary')->nullable(); // <--- Binario
            $table->foreign('purchase_id')->references('id')->on('purchases');
            
            // Transfer ID será referenciado después (en la migración de transfers) o aquí si ya existe
            // Para evitar errores circulares, lo dejamos nullable y añadimos la FK en la migración de transfers
            $table->char('transfer_id', 16)->charset('binary')->nullable(); 
            
            $table->char('branch_id', 16)->charset('binary');
            $table->foreign('branch_id')->references('id')->on('branches');
            
            $table->char('sku_id', 16)->charset('binary'); // <--- Binario
            $table->foreign('sku_id')->references('id')->on('skus');
            
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