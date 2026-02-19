<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    
    public function up(): void 
    {
        Schema::create('branches', function (Blueprint $table) {
            // CORRECCIÓN TÉCNICA: CHAR(36) / UUID NATIVO
            $table->uuid('id')->primary();
            
            $table->string('name')->unique(); 
            $table->string('city')->default('La Paz');
            $table->string('phone', 20)->nullable();
            $table->string('address')->nullable();
            
            $table->decimal('latitude', 10, 8)->nullable(); 
            $table->decimal('longitude', 11, 8)->nullable();
    
            $table->json('coverage_polygon')->nullable();
            $table->json('opening_hours')->nullable();
            
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
