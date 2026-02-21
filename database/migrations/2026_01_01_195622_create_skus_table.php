<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::create('skus', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained('products')->onDelete('cascade');
            $table->string('code')->nullable()->unique(); 
            $table->string('name'); 
            $table->decimal('base_price', 12, 2)->default(0); // Escala financiera corregida
            $table->decimal('weight', 8, 3)->default(0); 
            $table->decimal('conversion_factor', 8, 3)->default(1); // Precisión para stock corregida
            $table->string('image_path')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('skus');
    }
};