<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabla de Zonas
        Schema::create('market_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Ej: "Zona Norte (Fríos)"
            $table->string('slug')->unique();
            $table->string('hex_color')->default('#CCCCCC'); // Para UI
            $table->string('svg_id')->nullable(); // ID del grupo <g> en el SVG
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Modificar Categorías para pertenecer a una zona
        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('market_zone_id')
                  ->nullable()
                  ->constrained('market_zones')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['market_zone_id']);
            $table->dropColumn('market_zone_id');
        });
        Schema::dropIfExists('market_zones');
    }
};