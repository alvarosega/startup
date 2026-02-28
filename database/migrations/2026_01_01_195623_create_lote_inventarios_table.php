<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('branch_id')->constrained('branches'); 
            $table->foreignUuid('provider_id')->constrained('providers'); 
            $table->foreignUuid('admin_id')->constrained('admins');
            
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

        Schema::create('inventory_lots', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('purchase_id')->nullable()->constrained('purchases');
            $table->foreignUuid('transfer_id')->nullable(); // Se vincula luego si es necesario
            $table->foreignUuid('branch_id')->constrained('branches');
            $table->foreignUuid('sku_id')->constrained('skus');
            
            $table->string('lot_code')->unique();
            $table->integer('quantity');
            $table->integer('initial_quantity');
            $table->integer('reserved_quantity')->default(0);
            $table->boolean('is_safety_stock')->default(false)->index();
            
            
            $table->decimal('unit_cost', 10, 2);
            $table->date('expiration_date')->nullable()->index(); // Indexado para FEFO
            
            $table->timestamps();

            // ÍNDICE DE RENDIMIENTO EXTREMO PARA AGREGACIONES
            $table->index(['branch_id', 'sku_id', 'is_safety_stock', 'quantity', 'unit_cost'], 'idx_inventory_aggregation');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_lots');
        Schema::dropIfExists('purchases');
    }
};