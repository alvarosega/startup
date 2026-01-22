<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            
            // Relación recursiva (Padre -> Hijos)
            $table->foreignId('parent_id')
                  ->nullable()
                  ->constrained('categories')
                  ->onDelete('restrict'); // Evita borrar un padre si tiene hijos
            
            $table->string('name');
            $table->string('slug')->unique(); // INDISPENSABLE para SEO
            
            // Código ERP único (nullable permite varios nulos, pero no valores repetidos si existen)
            $table->string('external_code')->nullable()->unique(); 
            
            // Datos operativos
            $table->string('tax_classification')->nullable();
            $table->boolean('requires_age_check')->default(false);
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            
            // Assets y SEO
            $table->string('image_path')->nullable();
            $table->string('icon_path')->nullable(); // Por si usas iconos SVG en el futuro
            $table->string('bg_color', 7)->nullable();
            $table->text('description')->nullable(); // Texto interno o corto
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            
            $table->timestamps();
            $table->softDeletes(); // Papelera de reciclaje
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};