<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brand_category', function (Blueprint $table) {
            $table->foreignId('brand_id')->constrained('brands')->onDelete('cascade');
            $table->foreignUuid('category_id')->constrained('categories')->onDelete('cascade');
            
            // Llave primaria compuesta para evitar duplicados y mejorar performance
            $table->primary(['brand_id', 'category_id']); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brand_category');
    }
};