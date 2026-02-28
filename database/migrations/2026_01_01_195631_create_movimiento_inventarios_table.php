<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Sintaxis moderna, atómica y segura para UUIDs
            $table->foreignUuid('branch_id')->constrained('branches');
            $table->foreignUuid('sku_id')->constrained('skus');
            $table->foreignUuid('inventory_lot_id')->constrained('inventory_lots');
            
            // Polymorphic / Nullable fallback: Si una venta la hace el sistema por el cliente, 
            // puede que admin_id sea null. Lo dejaremos obligatorio por ahora, asumiendo 
            // que siempre hay un admin de sucursal despachando.
            $table->foreignUuid('admin_id')->constrained('admins');
            
            $table->string('type'); // ENTRY_PURCHASE, OUT_SALE, IN_RETURN, RESERVE, ADJUSTMENT
            $table->integer('quantity'); // Las salidas serán valores negativos (-5)
            $table->decimal('unit_cost', 10, 2); 
            $table->string('reference')->nullable(); // Ej: "Venta #ORD-5829"
            
            $table->timestamps();
            
            // Índice para consultas de Kardex ultrarrápidas
            $table->index(['branch_id', 'sku_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};