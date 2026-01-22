<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_removals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('sku_id')->constrained('skus');
            $table->foreignId('user_id')->constrained('users'); // Quien solicita
            $table->foreignId('approved_by')->nullable()->constrained('users'); // Quien aprueba
            
            $table->integer('cantidad');
            $table->string('motivo'); // Enum: Rotura, Vencimiento, etc.
            $table->text('observaciones')->nullable();
            $table->string('evidencia_url')->nullable(); // Foto opcional
            
            // Estados: 'pendiente', 'aprobado', 'rechazado'
            $table->string('estado')->default('pendiente');
            
            // Fecha cuando se procesó (aprobó/rechazó)
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_removals');
    }
};