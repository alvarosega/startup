<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        // 1. SKUs
        Schema::create('skus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            
            $table->string('code')->nullable()->unique(); // Código de Barras / EAN
            $table->string('name'); // Ej: "Botella 750ml"
            
            // Logística
            $table->decimal('weight', 8, 3)->default(0); // Kg
            $table->decimal('conversion_factor', 8, 2)->default(1); // Unidades por este SKU
            $table->string('image_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        
        Schema::dropIfExists('skus');
    }
};