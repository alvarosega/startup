<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('orders', function (Blueprint $table) {
            // 1. IDENTIDAD Y CONTROL
            $table->uuid('id')->primary();
            $table->string('code')->unique()->index(); // Ej: QR-10025
            
            // 2. RELACIONES (Aislamiento de Silos)
            $table->foreignUuid('customer_id')->constrained('customers');
            $table->foreignUuid('branch_id')->constrained('branches');
            $table->foreignUuid('driver_id')->nullable()->constrained('drivers');

            // 3. LOGÍSTICA Y ESTADOS
            $table->enum('delivery_type', ['pickup', 'delivery'])->default('pickup');
            $table->json('delivery_data')->nullable(); // Coordenadas, dirección técnica, notas
            
            $table->enum('status', [
                'pending_payment', 'under_review', 'preparing', 'dispatched', 
                'arrived', 'delivery_failed', 'completed', 'expired', 'cancelled'
            ])->default('pending_payment')->index(); 

            $table->string('delivery_otp', 4)->nullable(); // PIN de entrega
            $table->timestamp('reservation_expires_at')->nullable()->index(); 

            // 4. DESGLOSE FINANCIERO (Ley de Transparencia)
            $table->decimal('items_subtotal', 12, 2); // Suma neta de productos
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->decimal('service_fee', 10, 2)->default(0);
            $table->decimal('total_amount', 12, 2); // Total final (Suma de los 3 anteriores)
            
            // 5. PAGO Y FUTURA FACTURACIÓN
            $table->string('payment_method')->default('qr'); // qr, bank_transfer, cash
            $table->string('proof_of_payment')->nullable(); // Path del comprobante
            $table->string('bank_reference')->nullable();   // ID de transacción bancaria
            
            $table->string('billing_nit')->nullable();
            $table->string('billing_name')->nullable();

            // 6. AUDITORÍA DE REVISIÓN
            $table->text('rejection_reason')->nullable(); 
            $table->timestamp('reviewed_at')->nullable(); 
            
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignUuid('sku_id')->constrained('skus');
            
            // SNAPSHOTS (Blindaje contra cambios futuros en catálogo)
            $table->string('product_name');
            $table->string('sku_name');
            $table->string('image_snapshot')->nullable();

            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2); 
            $table->decimal('subtotal', 10, 2); // quantity * unit_price
        });
    }

    public function down(): void {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};