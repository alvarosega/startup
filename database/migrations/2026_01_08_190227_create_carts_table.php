<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // 1. EL CONTEXTO (Carts)
        Schema::create('carts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('session_id')->nullable()->index();
            
            // Implementación óptima con foreignUuid
            $table->foreignUuid('customer_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignUuid('branch_id')->constrained()->onDelete('cascade');
            
            $table->timestamps();
            $table->softDeletes();

            $table->index(['session_id', 'branch_id']);
        });

        // 2. EL CONTENIDO (CartItems)
        Schema::create('cart_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            $table->foreignUuid('cart_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('sku_id')->constrained()->onDelete('cascade');
            
            $table->integer('quantity')->default(1);
            $table->timestamps();
            
            $table->unique(['cart_id', 'sku_id']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
    }
};