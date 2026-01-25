<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('driver_profiles', function (Blueprint $table) {
            $table->id(); // O $table->uuid('id')->primary() si quieres todo UUID
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            
            $table->string('license_number')->unique();
            $table->string('license_plate');
            $table->enum('vehicle_type', ['moto', 'car', 'truck']);
            
            // Estados específicos del conductor
            $table->enum('status', ['pending', 'verified', 'rejected', 'suspended'])->default('pending');
            $table->text('rejection_reason')->nullable();
            
            // Rutas de documentos (se llenarán después en el Dashboard, pero preparamos el terreno)
            $table->string('ci_front_path')->nullable();
            $table->string('license_photo_path')->nullable();
            $table->string('vehicle_photo_path')->nullable();
    
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_profiles');
    }
};
