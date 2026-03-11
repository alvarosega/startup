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
            
            $table->uuid('customer_id');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            
            $table->uuid('product_id');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            
            // Índice único para evitar duplicados en favoritos
            $table->unique(['customer_id', 'product_id']);
            
            $table->timestamps(); // Útil para ordenar por "Añadido recientemente"
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};