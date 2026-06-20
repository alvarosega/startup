<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('brand_id')->index()->constrained('brands');
            $table->foreignUuid('category_id')->index()->constrained('categories');
            
            $table->string('name'); 
            $table->string('slug');
            $table->unsignedBigInteger('deleted_epoch')->default(0);
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false); // SOLUCIÓN: Columna inyectada
            $table->boolean('is_alcoholic')->default(false);
            $table->integer('sort_order')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'is_featured', 'sort_order'], 'idx_featured_lookup');
            $table->unique(['slug', 'deleted_epoch'], 'idx_products_slug_unique');
            $table->fullText('name', 'idx_product_name_fulltext');
        });
    }

    public function down(): void {
        Schema::dropIfExists('products');
    }
};