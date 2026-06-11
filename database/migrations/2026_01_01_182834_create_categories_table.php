<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('parent_id')->nullable()->index()->constrained('categories')->nullOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('external_code')->nullable();
            $table->unsignedBigInteger('deleted_epoch')->default(0);
            
            $table->string('tax_classification')->nullable();
            $table->boolean('requires_age_check')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            
            $table->string('image_path')->nullable();
            $table->string('icon_path')->nullable();
            $table->string('bg_color', 7)->nullable();
            $table->text('description')->nullable();
            
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'parent_id', 'sort_order'], 'idx_category_hierarchy');
            
            // Índices compuestos para asegurar integridad en registros vivos
            $table->unique(['slug', 'deleted_epoch'], 'idx_category_slug_unique');
            $table->unique(['external_code', 'deleted_epoch'], 'idx_category_code_unique');
        });
    }

    public function down(): void { 
        Schema::dropIfExists('categories'); 
    }
};