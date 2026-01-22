<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Perfiles (PII Separada)
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            
            // Datos Comunes
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('gender', 20)->nullable();
            
            // Datos específicos de Repartidor (🛵)
            $table->string('license_number')->nullable();
            $table->string('vehicle_type')->nullable(); 
            $table->string('license_plate', 15)->nullable();
            
            // Documentos (Fotos) - Agregados sin 'after'
            $table->string('ci_front_path')->nullable();
            $table->string('ci_back_path')->nullable();
            $table->string('license_photo_path')->nullable();
            $table->string('vehicle_photo_path')->nullable();
            
            // Estado de Verificación de Identidad
            $table->boolean('is_identity_verified')->default(false);
            
            $table->timestamps();
        });

        // 2. Identidades Sociales
        Schema::create('social_identities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('provider_name'); 
            $table->string('provider_id'); 
            $table->json('data_json')->nullable();
            $table->timestamps();
            $table->unique(['provider_name', 'provider_id']);
        });

        // 3. Historial de Login
        Schema::create('login_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('device_fingerprint')->nullable(); 
            $table->timestamp('login_at')->useCurrent();
        });

        // 4. DIRECCIONES DEL USUARIO
        Schema::create('user_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            $table->unsignedBigInteger('branch_id')->nullable(); 

            $table->string('alias')->nullable(); 
            $table->string('address');
            // COORDENADAS
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            
            $table->string('reference')->nullable(); 
            
            $table->boolean('is_default')->default(false); 
            
            $table->timestamps();
            $table->softDeletes();
        });

        // 5. DATOS DE FACTURACIÓN
        Schema::create('user_billing_infos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            $table->string('nit_number'); 
            $table->string('business_name'); 
            
            $table->boolean('is_default')->default(false); 
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_history');
        Schema::dropIfExists('social_identities');
        Schema::dropIfExists('user_profiles');
        Schema::dropIfExists('user_billing_infos');
        Schema::dropIfExists('user_addresses');
    }
};