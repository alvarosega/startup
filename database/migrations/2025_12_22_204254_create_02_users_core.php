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
            $table->char('id', 16)->charset('binary')->primary();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone', 20)->nullable();
            
            // CORRECCIÓN: branch_id debe ser binario para coincidir con la tabla branches
            $table->char('branch_id', 16)->charset('binary')->nullable()->index();
            
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role_level')->default('moderator');
            $table->boolean('is_active')->default(true);
            $table->string('mfa_secret')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();

            // Referencia explícita (Opcional, pero recomendada)
            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
        });

        // ... (Tablas de tokens admins igual que antes) ...
        Schema::create('admin_tokens', function (Blueprint $table) {
            $table->id(); 
            $table->char('admin_id', 16)->charset('binary');
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
            $table->char('id', 16)->charset('binary')->primary();
            $table->string('phone', 20)->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('status')->default('pending'); 
            $table->decimal('current_lat', 10, 8)->nullable();
            $table->decimal('current_lng', 11, 8)->nullable();
            $table->timestamps();
            $table->softDeletes(); 
        });

        // ... (Resto de tablas drivers igual que tu código original) ...
        Schema::create('driver_details', function (Blueprint $table) {
            $table->char('driver_id', 16)->charset('binary')->primary();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('license_number')->unique();
            $table->string('license_plate', 10);
            $table->string('vehicle_type'); 
            $table->timestamps();
            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('cascade');
        });
        
        // ... (driver_tokens, driver_billing_infos, password_reset_codes_drivers igual) ...
        Schema::create('driver_tokens', function (Blueprint $table) {
            $table->id();
            $table->char('driver_id', 16)->charset('binary');
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
            $table->char('driver_id', 16)->charset('binary');
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
            $table->char('id', 16)->charset('binary')->primary();
            
            // CORRECCIÓN: branch_id binario
            $table->char('branch_id', 16)->charset('binary')->nullable()->index();
            
            $table->string('phone', 20)->unique();
            $table->string('country_code', 3)->default('BO');
            $table->string('email')->unique();
            $table->string('password')->nullable(); 
            $table->integer('trust_score')->default(50);
            $table->boolean('is_active')->default(true);
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
            $table->index(['phone', 'country_code']);
            
            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
        });

        Schema::create('customer_profiles', function (Blueprint $table) {
            $table->char('customer_id', 16)->charset('binary')->primary();
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
            // CORRECCIÓN: ID Binario para la dirección también
            $table->char('id', 16)->charset('binary')->primary();
            
            $table->char('customer_id', 16)->charset('binary');
            
            // CORRECCIÓN: branch_id Binario
            $table->char('branch_id', 16)->charset('binary')->nullable(); 
            
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
            $table->char('customer_id', 16)->charset('binary');
            $table->string('provider_name'); 
            $table->string('provider_id'); 
            $table->json('data_json')->nullable();
            $table->timestamps();
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->unique(['provider_name', 'provider_id']);
        });

        Schema::create('customer_tokens', function (Blueprint $table) {
            $table->id();
            $table->char('customer_id', 16)->charset('binary');
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
            $table->char('customer_id', 16)->charset('binary');
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