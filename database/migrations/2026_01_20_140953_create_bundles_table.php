<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bundles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // VINCULACIÓN CON SUCURSAL
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            
            $table->string('name');
            // El slug debe ser único PERO por sucursal (opcional, o globalmente único)
            // Para evitar conflictos simples, lo dejamos único global, o unique(['slug', 'branch_id'])
            $table->string('slug'); 
            
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->decimal('fixed_price', 10, 2)->nullable();
            
            $table->softDeletes();
            $table->timestamps();

            // Índices para búsqueda rápida
            $table->index(['branch_id', 'is_active']);
            // Aseguramos que no haya dos slugs iguales en la misma sucursal
            $table->unique(['branch_id', 'slug']); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bundles');
    }
};