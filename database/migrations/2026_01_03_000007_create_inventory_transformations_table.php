<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('inventory_transformations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('branch_id')->constrained('branches');
            $table->foreignUuid('admin_id')->constrained('admins');
            
            // RECTIFICACIÓN: Enlace restrictivo a nivel de lotes origen/destino para salvaguardar la cola cronológica del FIFO
            $table->foreignUuid('source_inventory_lot_id')->constrained('inventory_lots')->restrictOnDelete();
            $table->decimal('quantity_removed', 12, 3); 
            
            $table->foreignUuid('destination_inventory_lot_id')->constrained('inventory_lots')->restrictOnDelete();
            $table->decimal('quantity_added', 12, 3); 
            
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('inventory_transformations');
    }
};