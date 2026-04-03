<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('(UUID())'));
            $table->foreignUuid('brand_id')->index()->constrained('brands');
            $table->foreignUuid('category_id')->index()->constrained('categories');
            $table->fullText('name', 'idx_product_name_fulltext');
            // LA LEY: Unicidad estricta. El slug es la identidad pública, el name la administrativa.
            $table->string('name')->unique(); 
            $table->string('slug')->unique();
            $table->boolean('is_featured')->default(true)->index();
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_alcoholic')->default(false);
            $table->integer('sort_order')->default(0)->index();
            $table->index(['is_featured', 'sort_order', 'is_active'], 'idx_featured_lookup');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};