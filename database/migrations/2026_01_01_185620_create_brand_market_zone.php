<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('brand_market_zone', function (Blueprint $table) {
            $table->foreignUuid('brand_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('market_zone_id')->constrained()->cascadeOnDelete();
            
            // Primary key compuesta para evitar duplicidad y optimizar JOINs
            $table->primary(['brand_id', 'market_zone_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('brand_market_zone'); }
};