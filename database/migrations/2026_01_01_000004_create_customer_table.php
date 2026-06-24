<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('branch_id')->nullable();
            $table->string('phone', 20);
            $table->string('country_code', 3)->default('BO');
            $table->string('email');
            $table->string('password')->nullable();
            $table->uuid('idempotency_key')->nullable()->unique(); 
            $table->integer('trust_score')->default(50);
            $table->boolean('is_active')->default(true); 
            $table->timestamp('email_verified_at')->nullable();
            $table->geometry('last_known_location', subtype: 'point')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->boolean('was_previously_deleted')->default(false);
            $table->boolean('needs_password_change')->default(false);
            
            // CORRECCIÓN OBLIGATORIA: Soporte para SessionGuard Remember Me
            $table->rememberToken();
            
            $table->unsignedBigInteger('deleted_epoch')->default(0);
            $table->timestamps();
            $table->softDeletes();
        
            $table->unique(['email', 'deleted_epoch']);
            $table->unique(['phone', 'deleted_epoch']);
            $table->index(['branch_id', 'is_active', 'created_at']);
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
            $table->uuid('branch_id')->nullable()->index();
            $table->string('alias')->nullable(); 
            $table->string('address');
            $table->geometry('position', subtype: 'point')->spatialIndex();
            $table->string('reference')->nullable(); 
            $table->boolean('is_default')->default(false); 
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
        });

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

        Schema::create('customer_billing_infos', function (Blueprint $table) {
            $table->id();
            $table->uuid('customer_id');
            $table->string('nit_number');
            $table->string('business_name');
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_billing_infos');
        Schema::dropIfExists('customer_socials');
        Schema::dropIfExists('customer_addresses');
        Schema::dropIfExists('customer_profiles');
        Schema::dropIfExists('customers');
    }
};