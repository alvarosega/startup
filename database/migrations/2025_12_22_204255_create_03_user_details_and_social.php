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
            $table->string('vehicle_type')->nullable(); // Moto, Automóvil, Camioneta, Bicicleta/Pie
            $table->string('license_plate', 15)->nullable();
        
            // Estado de Verificación de Identidad (Bloqueo de Seguridad)
            $table->boolean('is_identity_verified')->default(false);
            
            $table->timestamps();
        });
        // 2. Identidades Sociales
        Schema::create('social_identities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('provider_name'); // google, apple
            $table->string('provider_id'); // ID externo
            $table->json('data_json')->nullable();
            $table->timestamps();
            // Evitar duplicados del mismo proveedor
            $table->unique(['provider_name', 'provider_id']);
        });

        // 3. Historial de Login (Auditoría de Acceso)
        Schema::create('login_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('device_fingerprint')->nullable(); // Futura implementación
            $table->timestamp('login_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_history');
        Schema::dropIfExists('social_identities');
        Schema::dropIfExists('user_profiles');
    }
};