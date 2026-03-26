<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skus', function (Blueprint $table) {
            // IDENTIDAD ATÓMICA (UUIDv7 compatible)
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            
            // ATRIBUTOS DESCRIPTIVOS
            $table->string('name'); 
            $table->string('code')->nullable(); 
            
            // RECTIFICACIÓN: CAMPO MULTIMEDIA (Causa del error previo)
            $table->string('image_path')->nullable();
            
            // PARÁMETROS FINANCIEROS Y FÍSICOS (Indexados para filtrado rápido)
            $table->decimal('base_price', 12, 2)->default(0)->index();
            $table->decimal('weight', 8, 3)->default(0); 
            $table->decimal('conversion_factor', 8, 3)->default(1);
            
            // ESTADO Y ORDENAMIENTO
            $table->boolean('is_active')->default(true)->index();
            $table->integer('sort_order')->default(0)->index();
            
            // AUDITORÍA
            $table->timestamps();
            $table->softDeletes();
        
            // LA LEY: Unicidad física compuesta (Evita duplicados en registros vivos)
            $table->unique(['code', 'deleted_at'], 'idx_sku_code_unique');

            // PROTOCOLO ALTA DENSIDAD: FullText Search para MariaDB 11.8
            // Permite el uso de MATCH AGAINST en el ListProductsAction
            $table->fullText(['name', 'code'], 'idx_sku_search_fulltext');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skus');
    }
};