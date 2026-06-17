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
            
            // LEY: Se remueve la restricción única global para permitir transferencias de lotes
            $table->string('lot_code', 32);
            $table->decimal('quantity', 12, 3);
            $table->decimal('initial_quantity', 12, 3);
            $table->decimal('reserved_quantity', 12, 3)->default(0);
            
            $table->boolean('is_safety_stock')->default(false);
            // LEY: Columna unit_cost eliminada por definición de negocio
            $table->date('expiration_date')->nullable();
            $table->timestamps();

            $table->index(
                ['branch_id', 'sku_id', 'is_safety_stock', 'expiration_date'], 
                'idx_lots_fefo_lookup'
            );

            // LEY: Unicidad compuesta para restringir el mismo lote solo dentro de la misma sucursal
            $table->unique(['branch_id', 'lot_code'], 'idx_lots_branch_code_unique');
        });

        DB::statement('ALTER TABLE inventory_lots ADD SYSTEM VERSIONING');
    }

    public function down(): void {
        DB::statement('ALTER TABLE inventory_lots DROP SYSTEM VERSIONING');
        Schema::dropIfExists('inventory_lots');
    }
};