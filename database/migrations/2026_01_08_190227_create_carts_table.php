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
            $table->uuid('id')->primary();
            
            $table->string('session_id')->nullable()->index();
            
            $table->uuid('customer_id')->nullable(); 
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            
            $table->uuid('branch_id');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            
            $table->timestamps();
            $table->softDeletes();

            $table->index(['session_id', 'branch_id']);
            $table->index(['customer_id', 'branch_id']); 
        });

        // 2. EL CONTENIDO
        Schema::create('cart_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            $table->uuid('cart_id');
            $table->foreign('cart_id')->references('id')->on('carts')->onDelete('cascade');
            
            $table->uuid('sku_id');
            $table->foreign('sku_id')->references('id')->on('skus')->onDelete('cascade');
            
            $table->integer('quantity')->default(1);
            $table->timestamps();
            $table->unique(['cart_id', 'sku_id']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
    }
};
