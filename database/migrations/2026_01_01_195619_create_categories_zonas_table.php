<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. PRIMERO: Creamos la tabla independiente (Market Zones)
        Schema::create('market_zones', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('hex_color')->default('#CCCCCC'); 
            $table->string('svg_id')->nullable()->unique(); 
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. SEGUNDO: Creamos la tabla dependiente (Categories)
        Schema::create('categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Relación Jerárquica (Recursiva)
            $table->foreignUuid('parent_id')
                ->nullable()
                ->constrained('categories')
                ->nullOnDelete();
            
            // Relación con Zonas (Ahora la tabla ya existe)
            $table->foreignUuid('market_zone_id')
                ->nullable()
                ->constrained('market_zones')
                ->nullOnDelete();

            $table->string('name');
            $table->string('slug')->unique();
            $table->string('external_code')->nullable()->unique();
            
            // Configuración & Rendimiento
            $table->string('tax_classification')->nullable();
            $table->boolean('requires_age_check')->default(false);
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            
            // Assets & SEO
            $table->string('image_path')->nullable();
            $table->string('icon_path')->nullable();
            $table->string('bg_color', 7)->nullable();
            $table->text('description')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        // El orden inverso es vital para evitar errores de restricción al borrar
        Schema::dropIfExists('categories');
        Schema::dropIfExists('market_zones');
    }
};