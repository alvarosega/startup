<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::create('products', function (Blueprint $table) {
            // CORRECCIÓN: ID Binario
            $table->char('id', 16)->charset('binary')->primary();
            
            // CORRECCIÓN: Relaciones Binarias
            $table->char('brand_id', 16)->charset('binary');
            $table->foreign('brand_id')->references('id')->on('brands');

            $table->char('category_id', 16)->charset('binary');
            $table->foreign('category_id')->references('id')->on('categories');
            
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