<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('purchase_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('sku_id')->constrained('skus');
            
            // LEY: Escala de tres decimales (12,3) para mantener consistencia con inventory_lots
            $table->decimal('quantity', 12, 3); 
            // LEY: Sin columnas de costo unitario o subtotal por definición de negocio
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('purchase_items');
    }
};