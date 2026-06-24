<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('drivers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('branch_id')->nullable()->index();
            $table->string('phone', 20);
            $table->string('email');
            $table->string('password');
            $table->string('status')->default('pending'); 
            $table->boolean('was_previously_deleted')->default(false);
            $table->boolean('needs_password_change')->default(false);
            
            $table->boolean('is_online')->default(false);
            $table->boolean('is_available')->default(false);
            $table->timestamp('last_login_at')->nullable(); 
            $table->timestamp('last_seen_at')->nullable();
            
            $table->unsignedBigInteger('deleted_epoch')->default(0);
            $table->timestamps();
            $table->softDeletes(); 
            
            // Unicidad segura combinada
            $table->unique(['email', 'deleted_epoch']);
            $table->unique(['phone', 'deleted_epoch']);

            // Índice clave para el despachador de órdenes en tiempo real
            $table->index(['status', 'is_online', 'is_available']);

            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
        });

        Schema::create('driver_profiles', function (Blueprint $table) {
            $table->uuid('driver_id')->primary();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('license_number')->unique()->nullable();
            $table->string('license_plate', 10)->nullable();
            $table->string('vehicle_type')->nullable(); 
            $table->string('avatar_type')->default('icon'); 
            $table->string('avatar_source')->default('avatar_1.svg');
            $table->text('rejection_reason')->nullable();
            $table->string('ci_front_path')->nullable();
            $table->string('license_photo_path')->nullable();
            $table->string('vehicle_photo_path')->nullable();
            $table->timestamps();
            $table->softDeletes(); 

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
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_billing_infos');
        Schema::dropIfExists('driver_profiles'); 
        Schema::dropIfExists('drivers');
    }
};