<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            
            // Relación con el Cliente (UUID)
            $table->foreignUuid('customer_id')
                ->constrained('customers')
                ->cascadeOnDelete();
            
            // Relación con el SKU (UUID) - RECTIFICADO
            $table->foreignUuid('sku_id')
                ->constrained('skus')
                ->cascadeOnDelete();
            
            // LA LEY: Unicidad física para evitar duplicados en el nodo
            $table->unique(['customer_id', 'sku_id'], 'idx_unique_favorite');
            
            $table->timestamps(); // Auditoría para "Añadido recientemente"
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};