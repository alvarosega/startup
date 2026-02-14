<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::create('skus', function (Blueprint $table) {
            // CORRECCIÓN: ID Binario
            $table->char('id', 16)->charset('binary')->primary();
            
            // CORRECCIÓN: Product ID Binario
            $table->char('product_id', 16)->charset('binary');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            
            $table->string('code')->nullable()->unique(); 
            $table->string('name'); 
            $table->decimal('base_price', 10, 2)->default(0);
            $table->decimal('weight', 8, 3)->default(0); 
            $table->decimal('conversion_factor', 8, 2)->default(1); 
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