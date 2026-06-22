<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('skus', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            
            $table->string('name'); 
            $table->string('code'); 
            $table->string('image_path')->nullable();
            
            $table->decimal('base_price', 12, 2)->default(0)->index();
            $table->decimal('weight', 8, 3)->default(0); 
            $table->decimal('conversion_factor', 8, 3)->default(1);
            
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_bundle')->default(false)->index(); // LEY: Flag de control para ofertas/combos físicos
            $table->integer('sort_order')->default(0)->index();
            $table->unsignedBigInteger('deleted_epoch')->default(0);
            
            $table->timestamps();
            $table->softDeletes();
        
            $table->unique(['code', 'deleted_epoch'], 'idx_sku_code_unique');
            $table->fullText(['name', 'code'], 'idx_sku_search_fulltext');
        });
    }

    public function down(): void {
        Schema::dropIfExists('skus');
    }
};