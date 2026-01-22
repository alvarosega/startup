<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // ORD-2501-XK92
            
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('branch_id')->constrained('branches');
            
            // ESTADOS (Flow Logístico)
            $table->enum('status', [
                'pending_proof', // 1. Esperando foto del cliente
                'review',        // 2. Foto subida, Admin verificando dinero
                'confirmed',     // 3. Dinero en banco, Almacén preparando
                'dispatched',    // 4. En moto/camión
                'completed',     // 5. Entregado al cliente
                'cancelled'      // 0. Rechazado o Expirado
            ])->default('pending_proof')->index(); 
            
            // LOGÍSTICA DE RESERVA
            $table->timestamp('reservation_expires_at')->nullable()->index(); 
            
            // DINERO Y VALIDACIÓN
            $table->decimal('total_amount', 12, 2);
            $table->string('proof_of_payment')->nullable(); // Foto del voucher
            $table->text('rejection_reason')->nullable();   // Por si se rechaza
            
            // DATOS SNAPSHOT (Inmutables)
            $table->json('delivery_data'); 
            $table->foreignId('driver_id')->nullable()->constrained('users');
            
            // METRICAS
            $table->timestamp('reviewed_at')->nullable(); // Fecha cuando el cliente calificó
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('sku_id')->constrained('skus');
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2); 
            $table->decimal('subtotal', 10, 2);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};