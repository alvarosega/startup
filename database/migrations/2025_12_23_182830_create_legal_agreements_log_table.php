<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // =================================================================
        // SILO CUSTOMERS: Aceptación de Términos de Compra/Uso
        // =================================================================
        Schema::create('customer_legal_logs', function (Blueprint $table) {
            $table->id();
            
            $table->char('customer_id', 16)->charset('binary');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            
            $table->string('document_version', 20)->default('1.0'); // Ej: "TC-2026-V1"
            $table->string('ip_address', 45);
            $table->text('user_agent');
            $table->json('meta_data')->nullable(); // Para guardar ubicación GPS exacta al firmar si es necesario
            
            $table->timestamp('accepted_at')->useCurrent();
        });

        // =================================================================
        // SILO DRIVERS: Aceptación de Contratos de Adhesión/Servicio
        // =================================================================
        Schema::create('driver_legal_logs', function (Blueprint $table) {
            $table->id();
            
            $table->char('driver_id', 16)->charset('binary');
            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('cascade');
            
            $table->string('document_version', 20)->default('1.0'); // Ej: "DRIVER-CONTRACT-V2"
            $table->string('ip_address', 45);
            $table->text('user_agent');
            $table->json('device_snapshot')->nullable(); // Snapshot del dispositivo usado para firmar
            
            $table->timestamp('accepted_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_legal_logs');
        Schema::dropIfExists('customer_legal_logs');
        // Limpieza legacy
        Schema::dropIfExists('legal_agreements_log');
    }
};