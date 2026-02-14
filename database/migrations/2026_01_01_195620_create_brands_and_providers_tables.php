<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('providers', function (Blueprint $table) {
            // ID BINARIO
            $table->char('id', 16)->charset('binary')->primary();
            
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
    
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
    
            $table->timestamps();
            $table->softDeletes();
        });

        // Marcas (Brands)
        Schema::create('brands', function (Blueprint $table) {
            // CORRECCIÓN 1: ID propio debe ser Binario (antes era id() numérico)
            $table->char('id', 16)->charset('binary')->primary();
            
            // CORRECCIÓN 2: Relación con Provider debe ser Binaria (antes foreignUuid)
            $table->char('provider_id', 16)->charset('binary')->nullable();
            $table->foreign('provider_id')->references('id')->on('providers')->nullOnDelete();

            // Identidad
            $table->string('name')->unique();
            $table->string('slug')->unique();
            
            $table->string('manufacturer')->nullable();
            $table->char('origin_country_code', 2)->nullable(); 
            
            $table->string('tier')->default('Standard'); 
            
            $table->string('image_path')->nullable();
            $table->string('website')->nullable();
            
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);

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