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
            
            // ESTADO UNIFICADO
            $table->enum('status', [
                'pending_payment', // Esperando comprobante
                'under_review',    // Comprobante subido, esperando admin
                'preparing',       // Pago aprobado, tienda preparando
                'dispatched',      // En camino o Listo en Tienda
                'arrived',         // <--- NUEVO: Conductor en puerta
                'delivery_failed', // <--- NUEVO: Fallo en entrega (Sin PIN, etc)
                'completed',       // Entregado al cliente
                'expired',         // Timeout de 10 min superado
                'cancelled'        // Cancelado manualmente
            ])->default('pending_payment')->index(); 
            $table->string('delivery_otp', 4)->nullable(); // <--- NUEVO: PIN de 4 dígitos
            $table->timestamp('reservation_expires_at')->nullable()->index(); 

            // 4. FINANZAS Y COMPROBANTE ÚNICO
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->decimal('service_fee', 10, 2)->default(0);
            $table->decimal('total_amount', 12, 2); 
            $table->string('proof_of_payment')->nullable();

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