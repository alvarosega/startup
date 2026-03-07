<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // LA LEY: Integridad Relacional en cascada
            $table->foreignUuid('brand_id')->constrained('brands')->cascadeOnDelete();
            $table->foreignUuid('category_id')->constrained('categories')->cascadeOnDelete();
            
            // LA LEY: Indexación para rendimiento. La unicidad (unique) la maneja 
            // el StoreProductRequest ignorando los deleted_at.
            $table->string('name')->index(); 
            $table->string('slug')->index();
            
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            
            // Flags operacionales indexados para filtros rápidos
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_alcoholic')->default(false);
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};