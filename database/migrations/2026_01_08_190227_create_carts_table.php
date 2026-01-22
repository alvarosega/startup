<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. EL CONTEXTO (La "Cesta")
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            
            // Identificación Híbrida
            $table->string('session_id')->nullable()->index(); // Para Guests (Cookie)
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade'); // Para Registrados
            
            // Regla de Oro: Un carrito está atado a una sucursal física
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade'); 
            
            $table->timestamps();

            // Índice compuesto para búsquedas rápidas: "Dame el carrito de esta sesión en esta sucursal"
            $table->index(['session_id', 'branch_id']);
            $table->index(['user_id', 'branch_id']);
        });

        // 2. EL CONTENIDO (Los Items)
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained('carts')->onDelete('cascade');
            $table->foreignId('sku_id')->constrained('skus')->onDelete('cascade');
            
            $table->integer('quantity')->default(1);
            
            $table->timestamps();

            // Evitar duplicados: Si agrego el mismo SKU, sumo cantidad, no creo nueva fila
            $table->unique(['cart_id', 'sku_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
    }
};