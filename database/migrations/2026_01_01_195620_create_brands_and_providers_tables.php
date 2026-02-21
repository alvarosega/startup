<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {


        Schema::create('providers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('company_name'); 
            $table->string('commercial_name')->nullable();
            $table->string('tax_id')->unique(); 
            $table->string('internal_code')->nullable()->unique();
            $table->string('contact_name')->nullable();
            $table->string('email_orders')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->integer('lead_time_days')->default(1); 
            $table->decimal('min_order_value', 12, 2)->default(0); 
            $table->integer('credit_days')->default(0); 
            $table->decimal('credit_limit', 12, 2)->default(0);
            $table->boolean('is_active')->default(true)->index(); // Optimización de filtrado
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('brands', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Relación indispensable para el Seeder y lógica de negocio
            $table->foreignUuid('provider_id')->nullable()->constrained('providers')->nullOnDelete();
        
            $table->string('name')->unique();
            $table->string('slug')->unique();
            
            // Estandarización: image_path (igual que en Product/Sku) y website
            $table->string('image_path')->nullable();
            $table->string('website')->nullable();
            
            // Índices de rendimiento
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brands');
        Schema::dropIfExists('providers');
    }
};