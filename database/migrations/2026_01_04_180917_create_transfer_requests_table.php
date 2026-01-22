<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Cabecera (La Guía)
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            
            $table->string('code')->unique(); // TRF-2026-0001
            
            $table->foreignId('origin_branch_id')->constrained('branches');
            $table->foreignId('destination_branch_id')->constrained('branches');
            
            $table->foreignId('created_by')->constrained('users'); // Quien envió
            $table->foreignId('received_by')->nullable()->constrained('users'); // Quien recibió
            
            // Estados: 'in_transit' (Salió de A), 'completed' (Llegó a B)
            // No usaremos 'pending' porque al crearla ya descuenta stock de A
            $table->string('status')->default('in_transit'); 
            
            $table->text('notes')->nullable();
            
            $table->timestamp('shipped_at')->useCurrent();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
        });

        // 2. Detalle (Qué viaja y a qué precio)
        Schema::create('transfer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transfer_id')->constrained('transfers')->onDelete('cascade');
            $table->foreignId('sku_id')->constrained('skus');
            
            $table->integer('qty_sent'); // Lo que A dice que mandó
            $table->integer('qty_received')->nullable(); // Lo que B dice que llegó
            
            // IMPORTANTE: Guardamos el costo promedio al momento del envío.
            // Así, si el costo cambia mañana, esta transferencia respeta el valor histórico.
            $table->decimal('unit_cost', 10, 2); 
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transfer_items');
        Schema::dropIfExists('transfers');
    }
};