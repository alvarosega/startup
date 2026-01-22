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
        // 1. Cabecera
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // ORD-2501-AB12
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('branch_id')->constrained('branches');
            
            // ESTADOS DEL FLUJO
            // pending_proof: Se creó, corre el reloj de 5 min.
            // review: Cliente subió foto, reloj pausado.
            // confirmed: Admin aprobó, stock descontado oficialmente.
            // completed: Entregado.
            $table->enum('status', ['pending_proof', 'review', 'confirmed', 'dispatched', 'completed', 'cancelled'])->default('pending_proof');
            
            // LOGICA DE RESERVA TEMPORAL (SOFT RESERVE)
            $table->dateTime('reservation_expires_at')->nullable(); // Si status es pending_proof y pasamos esta hora -> Cancelar y devolver stock.
            
            // DINERO
            $table->decimal('total_amount', 12, 2);
            $table->string('proof_of_payment')->nullable(); // Foto del QR
            
            // LOGÍSTICA
            $table->json('delivery_data'); // Guardamos snapshot de la dirección: {address: "...", lat: -16.5, lng: -68.1}
            $table->foreignId('driver_id')->nullable()->constrained('users');
            
            $table->timestamps();
            $table->softDeletes();
        });
    
        // 2. Detalle
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('sku_id')->constrained('skus');
            
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2); // Precio congelado al momento de compra
            $table->decimal('subtotal', 10, 2);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
