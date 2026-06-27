<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('purchase_id')->constrained('purchases')->cascadeOnDelete();
            $table->foreignUuid('sku_id')->constrained('skus')->restrictOnDelete();
            
            $table->decimal('quantity', 12, 3); 
            // RECTIFICACIÓN: Inyección obligatoria de costo de entrada para trazabilidad y valoración FIFO
            $table->decimal('cost_price', 12, 2)->default(0.00);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('purchase_items');
    }
};