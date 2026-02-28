<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    
    public function up(): void 
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            $table->string('name')->unique(); 
            $table->string('city')->default('La Paz');
            $table->string('phone', 20)->nullable();
            $table->string('address')->nullable();
            
            $table->decimal('latitude', 10, 8)->nullable(); 
            $table->decimal('longitude', 11, 8)->nullable();
    
            $table->json('coverage_polygon')->nullable();
            $table->json('opening_hours')->nullable();
            
            // --- CONFIGURACIÓN LOGÍSTICA Y FINANCIERA ---
            $table->decimal('delivery_base_fee', 8, 2)->default(0.00);
            $table->decimal('delivery_price_per_km', 8, 2)->default(0.00);
            $table->decimal('surge_multiplier', 4, 2)->default(1.00);
            $table->decimal('min_order_amount', 8, 2)->default(0.00);
            $table->decimal('small_order_fee', 8, 2)->default(0.00);
            $table->decimal('base_service_fee_percentage', 5, 2)->default(0.00);
            // -------------------------------------------
            
            $table->boolean('is_default')->default(false)->index();
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void 
    {
        Schema::dropIfExists('branches');
    }
};