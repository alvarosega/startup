<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary(); // <--- CAMBIO RECOMENDADO A UUID PARA ORDENES (Más seguro)
            $table->string('code')->unique(); 
            
            $table->foreignUuid('user_id')->constrained('users');
            
            // CORRECCIÓN: Branch es INT
            $table->foreignId('branch_id')->constrained('branches');
            $table->enum('delivery_type', ['pickup', 'delivery'])->default('pickup');
            $table->enum('status', ['pending_proof', 'review', 'confirmed', 'dispatched', 'completed', 'cancelled'])->default('pending_proof')->index(); 
            
            $table->timestamp('reservation_expires_at')->nullable()->index(); 
            $table->decimal('total_amount', 12, 2);
            $table->string('proof_of_payment')->nullable(); 
            $table->text('rejection_reason')->nullable(); 
            
            $table->json('delivery_data'); 
            $table->foreignUuid('driver_id')->nullable()->constrained('users'); 
            
            $table->timestamp('reviewed_at')->nullable(); 
            $table->timestamps();
            $table->softDeletes();
        });
    
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            
            // Como Orders es UUID, aquí también
            $table->foreignUuid('order_id')->constrained('orders')->onDelete('cascade');
            
            // SKU es UUID
            $table->foreignUuid('sku_id')->constrained('skus');
            
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