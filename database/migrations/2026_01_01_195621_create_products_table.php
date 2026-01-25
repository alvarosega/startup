<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary(); // <--- CAMBIO A UUID (Estandarización)
            
            // Relaciones
            $table->foreignId('brand_id')->constrained('brands'); // Brands es INT, está bien así.
            
            // CORRECCIÓN DEL ERROR:
            $table->foreignUuid('category_id')->constrained('categories'); // Categories es UUID.
            
            $table->string('name')->unique(); 
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            
            $table->boolean('is_active')->default(true);
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