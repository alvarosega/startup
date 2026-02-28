<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // =================================================================================
        // SILO 1: ADMINS
        // =================================================================================
        Schema::create('admins', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone', 20)->nullable();
            
            $table->uuid('branch_id')->nullable()->index();
            
            $table->string('email')->unique();
            $table->string('password');

            // ELIMINADO: role_level (Spatie gestiona esto en model_has_roles)
            
            $table->boolean('is_active')->default(true);
            $table->string('mfa_secret')->nullable();
            
            // Campos de actividad (Correctos para los 3 silos)
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            
            $table->timestamps();

            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
        });

        // ... (Tablas de tokens admins igual que antes) ...
        Schema::create('admin_tokens', function (Blueprint $table) {
            $table->id(); 
            $table->uuid('admin_id');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
        });

        Schema::create('password_reset_codes_admins', function (Blueprint $table) {
            $table->string('email')->index();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // =================================================================================
        // SILO 2: DRIVERS (Sin cambios, ya estaba bien)
        // =================================================================================
        Schema::create('drivers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('phone', 20)->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('status')->default('pending'); 
            
            // ELIMINADOS: current_lat, current_lng (Movidos a Redis)
            // AÑADIDOS: Estados atómicos
            $table->boolean('is_online')->default(false);
            $table->boolean('is_available')->default(false);
            
            $table->timestamp('last_login_at')->nullable(); 
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
            $table->softDeletes(); 
        });

        Schema::create('driver_details', function (Blueprint $table) {
            $table->uuid('driver_id')->primary(); // PK es el mismo UUID del driver
            $table->string('first_name');
            $table->string('last_name');
            
            // Campos de Vehículo
            $table->string('license_number')->unique()->nullable();
            $table->string('license_plate', 10)->nullable();
            $table->string('vehicle_type')->nullable(); 

            // Campos de Interfaz
            $table->string('avatar_type')->default('icon'); 
            $table->string('avatar_source')->default('avatar_1.svg');
            
            // CONSOLIDACIÓN: Campos de verificación documental (Ex DriverProfile)
            $table->string('verification_status')->default('pending'); // pending, verified, rejected
            $table->text('rejection_reason')->nullable();
            $table->string('ci_front_path')->nullable();
            $table->string('license_photo_path')->nullable();
            $table->string('vehicle_photo_path')->nullable();

            $table->timestamps();

            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('cascade');
        });
        Schema::create('branch_driver', function (Blueprint $table) {
            $table->uuid('branch_id');
            $table->uuid('driver_id');
            $table->timestamps();
            
            // Llave primaria compuesta para evitar duplicados a nivel de DB
            $table->primary(['branch_id', 'driver_id']);
            
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('cascade');
        });

        // NUEVA TABLA: Historial GPS de alto rendimiento (Append-only)
        Schema::create('driver_location_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('driver_id')->index();
            $table->uuid('order_id')->nullable()->index(); // Nulo si estaba solo navegando
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->timestamp('created_at')->useCurrent();
            
            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('cascade');
            // Asumiendo que orders se crea después, de lo contrario mover esta llave foránea
            // $table->foreign('order_id')->references('id')->on('orders')->nullOnDelete();
        });
        
        // ... (driver_tokens, driver_billing_infos, password_reset_codes_drivers igual) ...
        Schema::create('driver_tokens', function (Blueprint $table) {
            $table->id();
            $table->uuid('driver_id');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('cascade');
        });

        Schema::create('driver_billing_infos', function (Blueprint $table) {
            $table->id();
            $table->uuid('driver_id');
            $table->string('nit_number');
            $table->string('business_name');
            $table->timestamps();
            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('cascade');
        });

        Schema::create('password_reset_codes_drivers', function (Blueprint $table) {
            $table->string('email')->index();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });


        // =================================================================================
        // SILO 3: CUSTOMERS
        // =================================================================================
        Schema::create('customers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('branch_id')->nullable()->index();
            
            $table->string('phone', 20)->unique();
            $table->string('country_code', 3)->default('BO');
            $table->string('email')->unique();
            $table->string('password')->nullable(); 
            $table->integer('trust_score')->default(50);
            $table->boolean('is_active')->default(true);
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
            $table->index(['phone', 'country_code']);
            
            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
        });

        Schema::create('customer_profiles', function (Blueprint $table) {
            $table->uuid('customer_id')->primary();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
        
            $table->date('birth_date')->nullable();
            $table->string('gender', 20)->nullable();
            $table->string('avatar_type')->default('icon'); 
            $table->string('avatar_source')->default('avatar_1.svg');
            $table->timestamps();
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
        });
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('customer_id');
            $table->uuid('branch_id')->nullable();
            
            $table->string('alias')->nullable(); 
            $table->string('address');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->string('reference')->nullable(); 
            $table->boolean('is_default')->default(false); 
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
        });

        // ... (Resto de tablas customer igual) ...
        Schema::create('customer_socials', function (Blueprint $table) {
            $table->id();
            $table->uuid('customer_id');
            $table->string('provider_name'); 
            $table->string('provider_id'); 
            $table->json('data_json')->nullable();
            $table->timestamps();
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->unique(['provider_name', 'provider_id']);
        });

        Schema::create('customer_tokens', function (Blueprint $table) {
            $table->id();
            $table->uuid('customer_id');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
        });

        Schema::create('customer_billing_infos', function (Blueprint $table) {
            $table->id();
            $table->uuid('customer_id');
            $table->string('nit_number');
            $table->string('business_name');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
        });

        Schema::create('password_reset_codes_customers', function (Blueprint $table) {
            $table->string('email')->index();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        // El orden de borrado es inverso a la creación (Hijos -> Padres)
        Schema::dropIfExists('password_reset_codes_customers');
        Schema::dropIfExists('customer_billing_infos');
        Schema::dropIfExists('customer_tokens');
        Schema::dropIfExists('customer_socials');
        Schema::dropIfExists('customer_addresses');
        Schema::dropIfExists('customer_profiles');
        Schema::dropIfExists('customers');

        Schema::dropIfExists('password_reset_codes_drivers');
        Schema::dropIfExists('driver_billing_infos');
        Schema::dropIfExists('driver_tokens');
        Schema::dropIfExists('driver_details');
        Schema::dropIfExists('drivers');

        Schema::dropIfExists('password_reset_codes_admins');
        Schema::dropIfExists('admin_tokens');
        Schema::dropIfExists('admins');
        
        Schema::dropIfExists('users');
        Schema::dropIfExists('user_profiles');
        Schema::dropIfExists('personal_access_tokens');
    }
};