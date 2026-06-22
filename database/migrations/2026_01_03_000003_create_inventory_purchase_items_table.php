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
            
            // Tres decimales para mantener consistencia absoluta con balanzas y factores de conversión fraccionados
            $table->decimal('quantity', 12, 3); 
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('purchase_items');
    }
};