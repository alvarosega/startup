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
            $table->foreignUuid('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            // --- PUNTOS DE ANCLAJE (Dónde se muestra) ---
            $table->foreignUuid('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignUuid('bundle_id')->nullable()->constrained('bundles')->nullOnDelete(); // <--- NUEVO: Banner en vista Bundle
            $table->unsignedInteger('version')->default(0);
            // --- TARGET (A dónde lleva al hacer clic) ---
            $table->uuidMorphs('target'); 
            
            $table->string('name');
            $table->string('image_mobile_path')->nullable();
            $table->string('image_desktop_path')->nullable();
            $table->enum('action_type', ['ADD_TO_CART', 'NAVIGATE'])->default('ADD_TO_CART');
            
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            
            $table->softDeletes();
            $table->timestamps();

            // --- LEY DE CONSULTAS: ÍNDICES COMPUESTOS ---
            
            // 1. Resolver para Categorías
            $table->index(['category_id', 'branch_id', 'is_active', 'sort_order'], 'idx_shop_category_resolver');

            // 2. Resolver para Bundles (Packs Atómicos o Templates)
            $table->index(['bundle_id', 'branch_id', 'is_active', 'sort_order'], 'idx_shop_bundle_resolver');

            // 3. Resolver para Placements Globales (Home/Landing)
            $table->index(['placement_id', 'branch_id', 'is_active', 'sort_order'], 'idx_shop_placement_resolver');
            $table->index(['brand_id', 'branch_id', 'is_active', 'sort_order'], 'idx_shop_brand_resolver');
        });
    }

    public function down(): void { Schema::dropIfExists('ad_creatives'); }
};