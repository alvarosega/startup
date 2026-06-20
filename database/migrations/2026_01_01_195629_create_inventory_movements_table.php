<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('branch_id')->constrained('branches');
            $table->foreignUuid('sku_id')->constrained('skus');
            $table->uuid('inventory_lot_id'); 
            $table->foreignUuid('admin_id')->constrained('admins');
            
            $table->string('type', 20); 
            $table->decimal('quantity', 12, 3);
            // LEY: Columna unit_cost eliminada por definición de negocio
            $table->string('reference')->nullable();
            // LEY: Nueva columna para auditoría histórica obligatoria en cuarentenas y mermas
            $table->string('reason')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['branch_id', 'sku_id', 'created_at'], 'idx_movements_kardex_lookup');
        });
    }

    public function down(): void {
        Schema::dropIfExists('inventory_movements');
    }
};