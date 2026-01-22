<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. LA ORDEN (Transacción)
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Ej: ORD-2501-XK92
            
            $table->foreignId('user_id')->constrained('users'); // Aquí el usuario YA debe existir (Login obligatorio)
            $table->foreignId('branch_id')->constrained('branches');
            
            // MÁQUINA DE ESTADOS
            $table->enum('status', [
                'pending_proof', // Stock descontado, esperando foto (5 min)
                'review',        // Foto subida, humano verificando
                'confirmed',     // Pago aprobado
                'dispatched',    // En camino
                'completed',     // Entregado
                'cancelled'      // Tiempo agotado o rechazado (Stock devuelto)
            ])->default('pending_proof')->index(); 
            
            // EL RELOJ DE LA MUERTE (Stock Reservation)
            // Si status es 'pending_proof' y pasamos esta fecha -> Cancelar y devolver stock.
            $table->timestamp('reservation_expires_at')->nullable()->index(); 
            
            // DINERO
            $table->decimal('total_amount', 12, 2);
            $table->string('proof_of_payment')->nullable(); // Path de la imagen
            
            // LOGÍSTICA (Snapshot de la dirección en ese momento)
            $table->json('delivery_data'); // { "address": "Calle 123", "lat": -16.5, "lng": -68.1, "ref": "Portón rojo" }
            $table->foreignId('driver_id')->nullable()->constrained('users');
            
            $table->timestamps();
            $table->softDeletes();
        });
    
        // 2. DETALLE DE LA ORDEN
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('sku_id')->constrained('skus');
            
            $table->integer('quantity');
            
            // PRECIO CONGELADO (Snapshot)
            // Guardamos cuánto costaba exactamente en el momento de la compra
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