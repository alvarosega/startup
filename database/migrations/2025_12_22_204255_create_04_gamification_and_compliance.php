<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Libro Mayor de Puntos (Exclusivo Customers)
        Schema::create('customer_loyalty_ledger', function (Blueprint $table) {
            $table->id();
            
            // Relación con CUSTOMERS (Binary)
            $table->char('customer_id', 16)->charset('binary');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            
            $table->integer('amount'); 
            $table->string('type'); // 'earned', 'redeemed', 'expired'
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            
            // Índices para velocidad de cálculo de saldo
            $table->index(['customer_id', 'created_at']);
        });

        // 2. Verificaciones de Identidad (KYC - Customers)
        Schema::create('customer_verifications', function (Blueprint $table) {
            $table->id();
            
            // El sujeto a verificar es un CUSTOMER
            $table->char('customer_id', 16)->charset('binary');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            
            // El revisor es un ADMIN (Internal Staff)
            $table->char('reviewer_id', 16)->charset('binary')->nullable();
            $table->foreign('reviewer_id')->references('id')->on('admins');
            
            $table->string('front_ci_path')->nullable();
            $table->string('back_ci_path')->nullable();
            $table->string('selfie_path')->nullable();
            
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            
            // Asumo que la tabla rejection_reasons existe en una migración previa (03 o similar).
            // Si no existe, elimina la linea del foreign y deja solo el integer o string.
            $table->foreignId('rejection_reasons_id')->nullable(); 
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_verifications');
        Schema::dropIfExists('customer_loyalty_ledger');
    }
};