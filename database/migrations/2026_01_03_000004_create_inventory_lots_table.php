<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('inventory_lots', function (Blueprint $table) {
            $table->uuid('id')->primary(); // Generación secuencial UUIDv7 en App
            $table->foreignUuid('purchase_id')->nullable()->constrained('purchases')->nullOnDelete();
            $table->foreignUuid('transfer_id')->nullable()->index(); 
            $table->foreignUuid('branch_id')->constrained('branches')->restrictOnDelete();
            $table->foreignUuid('sku_id')->constrained('skus')->restrictOnDelete();
            
            $table->string('lot_code', 32);
            
            // RECTIFICACIÓN: Segmentación numérica explícita del stock ordinario vs stock de seguridad dentro del mismo lote
            $table->decimal('quantity', 12, 3); // Disponible ordinario actual
            $table->decimal('initial_quantity', 12, 3); // Histórico ordinario ingresado
            
            $table->decimal('safety_quantity', 12, 3)->default(0.000); // Disponible de seguridad actual (colchón manual)
            $table->decimal('initial_safety_quantity', 12, 3)->default(0.000); // Histórico de seguridad asignado
            
            $table->decimal('reserved_quantity', 12, 3)->default(0.000); // Apartado temporal (durante la ventana de 10 min)
            
            $table->boolean('is_quarantine')->default(false)->index(); 
            $table->date('expiration_date')->nullable();
            $table->timestamps();

            // Índice FEFO compuesto optimizado para búsquedas de lotes despachables omitiendo stock retenido
            $table->index(
                ['branch_id', 'sku_id', 'is_quarantine', 'expiration_date'], 
                'idx_lots_fefo_lookup'
            );

            // Garantizar que no existan códigos de lote duplicados dentro del mismo nodo logístico
            $table->unique(['branch_id', 'lot_code'], 'idx_lots_branch_code_unique');
        });
    }

    public function down(): void {
        Schema::dropIfExists('inventory_lots');
    }
};