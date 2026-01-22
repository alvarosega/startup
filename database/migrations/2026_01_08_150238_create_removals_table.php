<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Cabecera (La Solicitud)
        Schema::create('removal_requests', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // REM-2601-A1B2
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('user_id')->constrained('users'); // Quien solicita (Manager)
            
            // Estado del flujo
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users'); // Super Admin
            $table->timestamp('approved_at')->nullable();

            // Motivo tipificado
            $table->enum('reason', [
                'expiration',   // Vencimiento
                'damage',       // Daño/Rotura
                'theft',        // Robo/Faltante
                'internal_use', // Consumo Interno/Degustación
                'admin_error'   // Error Administrativo
            ]);
            
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 2. Detalle (Vínculo exacto con el Lote para reservar)
        Schema::create('removal_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('removal_request_id')->constrained('removal_requests')->onDelete('cascade');
            
            // Vinculamos DIRECTO al lote para bloquear ese stock específico
            $table->foreignId('inventory_lot_id')->constrained('inventory_lots');
            
            $table->decimal('quantity', 10, 2); // Cantidad a dar de baja
            $table->decimal('unit_cost', 10, 2); // Costo histórico para reportar pérdidas ($)
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('removal_items');
        Schema::dropIfExists('removal_requests');
    }
};