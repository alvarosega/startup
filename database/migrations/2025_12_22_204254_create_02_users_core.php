<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabla Principal USERS
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUID Primary Key
            
            $table->string('phone', 20)->unique(); 
            $table->string('country_code', 3)->default('BO');
            $table->string('email')->nullable()->unique();
            $table->string('password')->nullable();
            
            // Security & Trust
            $table->integer('trust_score')->default(50);
            $table->boolean('is_active')->default(true);
            
            // Gamification
            $table->unsignedBigInteger('current_level_id')->nullable(); 

            // Meta
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('avatar_type')->default('icon'); 
            $table->string('avatar_source')->default('avatar_1.svg');
            
            $table->rememberToken();
            $table->timestamps();
            
            // Índices
            $table->index(['phone', 'country_code']); 
            $table->index('email');
        });

        // 2. Perfiles (Datos Personales Humanos)
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id(); 
            
            // CRÍTICO: Referencia a UUID correcta
            $table->foreignUuid('user_id')->unique()->constrained()->onDelete('cascade');
            
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('gender', 20)->nullable();
            
            // KYC Images (Solo identidad personal)
            $table->string('ci_front_path')->nullable();
            $table->string('ci_back_path')->nullable();
            
            // NOTA: Los datos de licencia/vehículo se movieron a 'driver_profiles'
            
            $table->boolean('is_identity_verified')->default(false);
            $table->timestamps();
        });

        // 3. Identidades Sociales
        Schema::create('social_identities', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
            $table->string('provider_name'); 
            $table->string('provider_id'); 
            $table->json('data_json')->nullable();
            $table->timestamps();
            $table->unique(['provider_name', 'provider_id']);
        });

        // 4. Historial Login
        Schema::create('login_history', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('device_fingerprint')->nullable(); 
            $table->timestamp('login_at')->useCurrent();
            
            $table->index(['user_id', 'login_at']);
        });

        // 5. Direcciones
        Schema::create('user_addresses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
            
            $table->unsignedBigInteger('branch_id')->nullable(); 

            $table->string('alias')->nullable(); 
            $table->string('address');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->string('reference')->nullable(); 
            $table->boolean('is_default')->default(false); 
            $table->timestamps();
            $table->softDeletes();
        });

        // 6. Facturación
        Schema::create('user_billing_infos', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
            $table->string('nit_number'); 
            $table->string('business_name'); 
            $table->boolean('is_default')->default(false); 
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_billing_infos');
        Schema::dropIfExists('user_addresses');
        Schema::dropIfExists('login_history');
        Schema::dropIfExists('social_identities');
        Schema::dropIfExists('user_profiles');
        Schema::dropIfExists('users');
    }
};