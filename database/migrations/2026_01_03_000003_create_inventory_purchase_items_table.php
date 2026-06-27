<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('purchase_id')->constrained('purchases')->restrictOnDelete(); // RECTIFICACIÓN: Se revoca la cascada física
            $table->foreignUuid('sku_id')->constrained('skus')->restrictOnDelete();
            
            $table->decimal('quantity', 12, 3); 
            $table->decimal('cost_price', 12, 2)->default(0.00);
            
            $table->unsignedBigInteger('deleted_epoch')->default(0);
            $table->timestamps();
            $table->softDeletes(); // RECTIFICACIÓN: Soporte de borrado lógico sincronizado

            $table->index(['purchase_id', 'sku_id'], 'idx_purchase_items_lookup');
        });
    }

    public function down(): void {
        Schema::dropIfExists('purchase_items');
    }
};