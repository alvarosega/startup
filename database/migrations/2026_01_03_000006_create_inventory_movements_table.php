<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->uuid('id')->primary(); // Generación secuencial UUIDv7 en App
            $table->foreignUuid('branch_id')->constrained('branches')->restrictOnDelete();
            $table->foreignUuid('sku_id')->constrained('skus')->restrictOnDelete();
            
            // RECTIFICACIÓN: Restricción foránea estricta para impedir la destrucción de la trazabilidad del Kardex
            $table->foreignUuid('inventory_lot_id')->constrained('inventory_lots')->restrictOnDelete(); 
            $table->foreignUuid('admin_id')->constrained('admins')->restrictOnDelete();
            
            $table->string('type', 25)->index(); // Exclusivamente los estados validados del protocolo
            $table->decimal('quantity', 12, 3);
            $table->string('reference')->nullable();
            $table->string('reason')->nullable(); // Justificación obligatoria para movimientos manuales (cuarentenas, mermas, rescates)
            $table->timestamp('created_at')->useCurrent();

            // Índice de cobertura para optimización de reportes cronológicos de inventario y auditorías
            $table->index(['branch_id', 'sku_id', 'created_at'], 'idx_movements_kardex_lookup');
        });
    }

    public function down(): void {
        Schema::dropIfExists('inventory_movements');
    }
};