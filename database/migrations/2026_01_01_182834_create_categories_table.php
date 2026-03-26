<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('external_code')->nullable()->unique();
            
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
            
            // --- AUDITORÍA Y ESTADO (Única declaración) ---
            $table->timestamps();
            $table->softDeletes();

            // --- LEY DE CONSULTAS: ÍNDICES COMPUESTOS ---
            
            // 1. Carga de menús y jerarquías (Admin/Customer)
            $table->index(['is_active', 'parent_id', 'sort_order'], 'idx_active_hierarchy');
            
            // 2. Resolución rápida por Slug (Punto de entrada único)
            $table->index(['slug', 'is_active'], 'idx_customer_slug_lookup');
        });
    }

    public function down(): void { 
        Schema::dropIfExists('categories'); 
    }
};