<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('ad_creatives', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('campaign_id')->constrained('ad_campaigns')->cascadeOnDelete();
            $table->foreignUuid('placement_id')->constrained('ad_placements')->cascadeOnDelete();
            $table->foreignUuid('branch_id')->constrained('branches')->cascadeOnDelete();
            
            // --- PUNTOS DE ANCLAJE OPTIMIZADOS (Columnas planas para indexación de alta velocidad) ---
            $table->foreignUuid('sku_id')->nullable()->constrained('skus')->nullOnDelete(); // RECTIFICACIÓN: Soporte para páginas de producto
            $table->foreignUuid('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignUuid('bundle_id')->nullable()->constrained('bundles')->nullOnDelete(); 
            $table->foreignUuid('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            
            $table->unsignedInteger('version')->default(0);
            
            // --- TARGET ENCAPSULADO (Estrictamente interno mediante UUID Morphs) ---
            $table->uuidMorphs('target'); 
            
            $table->string('name', 128);
            $table->string('image_mobile_path')->nullable();
            $table->string('image_desktop_path')->nullable();
            $table->enum('action_type', ['ADD_TO_CART', 'NAVIGATE'])->default('ADD_TO_CART');
            
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            $table->softDeletes();

            // --- LEY DE CONSULTAS: ÍNDICES COMPUESTOS PREALINEADOS ---
            
            // 1. Resolución Contextual en Detalle de SKU
            $table->index(['sku_id', 'branch_id', 'is_active', 'sort_order'], 'idx_ads_sku_resolver');

            // 2. Resolución Contextual en Árbol de Categorías
            $table->index(['category_id', 'branch_id', 'is_active', 'sort_order'], 'idx_ads_category_resolver');

            // 3. Resolución Contextual en Vistas de Combos Dinámicos
            $table->index(['bundle_id', 'branch_id', 'is_active', 'sort_order'], 'idx_ads_bundle_resolver');

            // 4. Resolución Contextual por Filtros de Marca
            $table->index(['brand_id', 'branch_id', 'is_active', 'sort_order'], 'idx_ads_brand_resolver');

            // 5. Resolución para Espacios Globales Monetizados (Home Hero, Buscador, Carrito, Éxito)
            $table->index(['placement_id', 'branch_id', 'is_active', 'sort_order'], 'idx_ads_placement_resolver');
        });
    }

    public function down(): void { 
        Schema::dropIfExists('ad_creatives'); 
    }
};