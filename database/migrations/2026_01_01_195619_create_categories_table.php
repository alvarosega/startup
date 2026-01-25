<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->uuid('id')->primary(); // <--- UUID
            
            // Relación Recursiva (UUID)
            $table->foreignUuid('parent_id')
                  ->nullable()
                  ->constrained('categories')
                  ->nullOnDelete(); // Si borran el padre, los hijos quedan huérfanos (raíz)
            
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('external_code')->nullable()->unique();
            
            // Configuración
            $table->string('tax_classification')->nullable();
            $table->boolean('requires_age_check')->default(false);
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            
            // Assets / SEO
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
        Schema::dropIfExists('categories');
    }
};