<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('providers', function (Blueprint $table) {
            $table->id();
            
            // Identidad
            $table->string('company_name'); // Razón Social
            $table->string('commercial_name')->nullable(); // Nombre Comercial (Marketing)
            $table->string('tax_id')->unique(); // NIT/RUC
            $table->string('internal_code')->nullable()->unique(); // CORREGIDO: Debe ser único
            
            // Contacto
            $table->string('contact_name')->nullable();
            $table->string('email_orders')->nullable(); // Para envío automático de POs
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            
            // Reglas de Negocio
            $table->integer('lead_time_days')->default(1); 
            $table->decimal('min_order_value', 12, 2)->default(0); 
            $table->integer('credit_days')->default(0); 
            $table->decimal('credit_limit', 12, 2)->default(0);

            // Control
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        // Marcas (Brands) - Depende de Providers
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            
            // Relación
            $table->foreignId('provider_id')
                  ->nullable()
                  ->constrained('providers')
                  ->nullOnDelete();

            // Identidad
            $table->string('name')->unique();
            $table->string('slug')->unique();
            
            // Detalles del Fabricante
            $table->string('manufacturer')->nullable();
            $table->char('origin_country_code', 2)->nullable(); // ISO: BO, US, GB
            
            // ESTA ES LA LÍNEA QUE FALTABA:
            $table->string('tier')->default('Standard'); // Economy, Standard, Premium, Luxury
            
            // Assets y Marketing
            $table->string('image_path')->nullable();
            $table->string('website')->nullable();
            
            // Control
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