<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('branch_id')->constrained('branches')->restrictOnDelete();
            $table->foreignUuid('sku_id')->constrained('skus')->restrictOnDelete();
            $table->foreignUuid('inventory_lot_id')->constrained('inventory_lots')->restrictOnDelete(); 
            $table->foreignUuid('admin_id')->constrained('admins')->restrictOnDelete();
            
            $table->string('type', 25)->index(); 
            $table->decimal('quantity', 12, 3);
            // RECTIFICACIÓN: Persistencia estática del remanente inmediato, optimizando auditorías de saldo a costo O(1)
            $table->decimal('balance_after', 12, 3);
            $table->string('reference')->nullable();
            $table->string('reason')->nullable(); 
            $table->timestamp('created_at')->useCurrent();

            $table->index(['branch_id', 'sku_id', 'created_at'], 'idx_movements_kardex_lookup');
        });
    }

    public function down(): void {
        Schema::dropIfExists('inventory_movements');
    }
};