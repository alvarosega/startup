<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('brands', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('(UUID())'));
            $table->foreignUuid('parent_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->foreignUuid('provider_id')->constrained('providers')->cascadeOnDelete();
            $table->foreignUuid('category_id')->constrained('categories')->cascadeOnDelete();

            $table->string('name'); 
            $table->string('slug')->unique();
            $table->string('bg_color', 7)->nullable()->comment('Hexadecimal color para el sistema de resplandor');
            $table->string('image_path')->nullable();
            $table->string('website')->nullable();
            
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->text('description')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            // LEY DE CONSULTAS: Filtrado por categoría y estado
            $table->index(['is_active', 'category_id', 'sort_order'], 'idx_brands_active_cat');
        });
    }
    public function down(): void { Schema::dropIfExists('brands'); }
};