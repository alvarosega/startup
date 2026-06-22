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
            
            // --- ENLACES PLANOS DE REDIRECCIÓN DIRECTA (Sustituyen al motor polimórfico) ---
            $table->foreignUuid('sku_id')->nullable()->constrained('skus')->nullOnDelete();
            $table->foreignUuid('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignUuid('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            
            // RECTIFICACIÓN: Enlace al macro-agrupador para automatización de inyección al carrito
            $table->foreignUuid('bundle_id')->nullable()->constrained('bundles')->cascadeOnDelete(); 
            
            $table->string('name', 128);
            $table->string('image_mobile_path')->nullable();
            $table->string('image_desktop_path')->nullable();
            $table->enum('action_type', ['ADD_TO_CART', 'NAVIGATE'])->default('ADD_TO_CART');
            
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            $table->softDeletes();

            // --- ÍNDICES COMPUESTOS PREALINEADOS PARA BÚSQUEDA Y RESOLUCIÓN CONTEXTUAL ---
            $table->index(['sku_id', 'branch_id', 'is_active', 'sort_order'], 'idx_ads_sku_resolver');
            $table->index(['category_id', 'branch_id', 'is_active', 'sort_order'], 'idx_ads_category_resolver');
            $table->index(['brand_id', 'branch_id', 'is_active', 'sort_order'], 'idx_ads_brand_resolver');
            $table->index(['bundle_id', 'branch_id', 'is_active', 'sort_order'], 'idx_ads_bundle_resolver');
            $table->index(['placement_id', 'branch_id', 'is_active', 'sort_order'], 'idx_ads_placement_resolver');
        });
    }

    public function down(): void { 
        Schema::dropIfExists('ad_creatives'); 
    }
};