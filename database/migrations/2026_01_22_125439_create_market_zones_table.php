<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('market_zones', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            
            // --- AÑADIR ESTOS CAMPOS ---
            $table->string('hex_color')->default('#CCCCCC'); 
            $table->string('svg_id')->nullable(); 
            $table->text('description')->nullable();
            // ---------------------------
    
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    
        Schema::table('categories', function (Blueprint $table) {
            $table->uuid('market_zone_id')->nullable();
            $table->foreign('market_zone_id')->references('id')->on('market_zones')->onDelete('set null');
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
