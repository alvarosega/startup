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
            // CORRECCIÓN: ID Binario
            $table->char('id', 16)->charset('binary')->primary();
            
            $table->string('name'); 
            $table->string('slug')->unique();
            $table->string('hex_color')->default('#CCCCCC'); 
            $table->string('svg_id')->nullable(); 
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Modificar Categorías para pertenecer a una zona
        Schema::table('categories', function (Blueprint $table) {
            // CORRECCIÓN: Relación Binaria
            $table->char('market_zone_id', 16)->charset('binary')->nullable();
            $table->foreign('market_zone_id')->references('id')->on('market_zones')->nullOnDelete();
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