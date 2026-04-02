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
            $table->foreignUuid('customer_id')->constrained('customers')->cascadeOnDelete();
            
            // CAMBIO VITAL: El favorito se ancla al Producto, no al SKU individual
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            
            $table->unique(['customer_id', 'product_id'], 'idx_unique_favorite_product');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};