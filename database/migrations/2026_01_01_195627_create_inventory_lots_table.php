<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        Schema::create('inventory_lots', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('purchase_id')->nullable()->constrained('purchases');
            $table->foreignUuid('transfer_id')->nullable(); 
            $table->foreignUuid('branch_id')->constrained('branches');
            $table->foreignUuid('sku_id')->constrained('skus');
            
            $table->string('lot_code', 32);
            $table->decimal('quantity', 12, 3);
            $table->decimal('initial_quantity', 12, 3);
            $table->decimal('reserved_quantity', 12, 3)->default(0);
            
            $table->boolean('is_safety_stock')->default(false);
            $table->boolean('is_quarantine')->default(false)->index(); // RECTIFICACIÓN: Flag de origen para stock retenido
            $table->date('expiration_date')->nullable();
            $table->timestamps();

            // Índice FEFO optimizado incluyendo el nuevo flag de cuarentena
            $table->index(
                ['branch_id', 'sku_id', 'is_safety_stock', 'is_quarantine', 'expiration_date'], 
                'idx_lots_fefo_lookup'
            );

            $table->unique(['branch_id', 'lot_code'], 'idx_lots_branch_code_unique');
        });


    }

    public function down(): void {

    
        Schema::dropIfExists('inventory_lots');
    }
}; 