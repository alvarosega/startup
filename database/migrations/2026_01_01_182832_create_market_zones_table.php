<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('market_zones', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('hex_color', 7)->default('#CCCCCC'); 
            $table->string('svg_id')->nullable()->unique(); 
            $table->text('description')->nullable();
            
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            $table->softDeletes();

            // Índice de búsqueda operativa
            $table->index(['is_active', 'slug']);
        });
    }
    public function down(): void { Schema::dropIfExists('market_zones'); }
};