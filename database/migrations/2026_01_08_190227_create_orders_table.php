<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('(UUID())'));
            $table->string('code')->unique()->index(); 
            
            $table->foreignUuid('customer_id')->constrained('customers');
            $table->foreignUuid('branch_id')->constrained('branches');
            $table->foreignUuid('driver_id')->nullable()->constrained('drivers');

            $table->enum('delivery_type', ['pickup', 'delivery'])->default('pickup');
            $table->json('delivery_data')->nullable(); // Snapshot de dirección y coordenadas
            
            // RECTIFICACIÓN: Máquina de Estados Oficial
            $table->enum('status', [
                'pending', 'payment_pending', 'rejected', 'confirmed', 
                'preparing', 'ready_for_dispatch', 'dispatched', 
                'delivered', 'cancelled', 'returned'
            ])->default('pending')->index(); 

            $table->string('pickup_otp', 5)->nullable();  
            $table->string('delivery_otp', 4)->nullable(); 
            
            // Expiración exclusiva para el estado 'pending'
            $table->timestamp('reservation_expires_at')->nullable()->index(); 

            $table->decimal('items_subtotal', 12, 2);
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->decimal('service_fee', 10, 2)->default(0);
            $table->decimal('total_amount', 12, 2); 
            
            $table->string('payment_method')->default('qr'); 
            $table->string('proof_of_payment')->nullable(); 
            $table->string('bank_reference')->nullable();   
            $table->string('billing_nit')->nullable();
            $table->string('billing_name')->nullable();
            
            $table->text('rejection_reason')->nullable(); 
            $table->timestamp('reviewed_at')->nullable(); 
            
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('(UUID())'));
            $table->foreignUuid('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignUuid('sku_id')->constrained('skus');
            $table->string('product_name');
            $table->string('sku_name');
            $table->string('image_snapshot')->nullable();
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