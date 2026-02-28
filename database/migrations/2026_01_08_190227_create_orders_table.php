<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('orders', function (Blueprint $table) {
            // 1. IDENTIDAD
            $table->uuid('id')->primary();
            $table->string('code')->unique(); 
            
            // 2. SILOS INDEPENDIENTES (Aislamiento Zero-Trust)
            $table->foreignUuid('customer_id')->constrained('customers');
            $table->foreignUuid('branch_id')->constrained('branches');
            $table->foreignUuid('driver_id')->nullable()->constrained('drivers');

            // 3. LOGÍSTICA PÚRA
            $table->enum('delivery_type', ['pickup', 'delivery'])->default('pickup');
            $table->json('delivery_data')->nullable(); 
            
            // ESTADO LOGÍSTICO (Dónde está el paquete)
            $table->enum('status', [
                'pending_payment', // Esperando el pago (Total o Anticipo)
                'preparing',       // Pago inicial aprobado, tienda preparando
                'dispatched',      // En camino (Delivery) o Listo en Tienda (PickUp)
                'completed',       // Entregado al cliente
                'expired',         // Timeout de 10 min superado
                'cancelled'        // Cancelado manualmente
            ])->default('pending_payment')->index(); 
            
            $table->timestamp('reservation_expires_at')->nullable()->index(); 
            
            // 4. LEDGER FINANCIERO ESTRICTO
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->decimal('service_fee', 10, 2)->default(0);
            $table->decimal('total_amount', 12, 2); 
            
            $table->enum('payment_type', ['total', 'partial']);
            $table->decimal('advance_amount', 12, 2)->default(0); // Anticipo (o Total si aplica)
            $table->decimal('balance_amount', 12, 2)->default(0); // Saldo pendiente
            
            // COMPROBANTE 1 (Adelanto o Pago Total)
            $table->string('advance_proof')->nullable(); 
            $table->enum('advance_status', [
                'pending', 'under_review', 'approved', 'rejected'
            ])->default('pending');

            // COMPROBANTE 2 (Saldo Restante en Tránsito/Puerta)
            $table->string('balance_proof')->nullable(); 
            $table->enum('balance_status', [
                'none', 'pending', 'under_review', 'approved', 'rejected'
            ])->default('none');

            // 5. AUDITORÍA Y CONTROL
            $table->string('bank_reference')->nullable(); // Para el Admin
            $table->text('rejection_reason')->nullable(); // Razón genérica de rechazo
            $table->timestamp('reviewed_at')->nullable(); 
            
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignUuid('sku_id')->constrained('skus');
            
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2); 
            $table->decimal('subtotal', 10, 2);
        });
    }

    public function down(): void {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};