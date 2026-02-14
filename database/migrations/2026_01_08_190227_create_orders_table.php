<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->char('id', 16)->charset('binary')->primary(); // <--- Binario
            $table->string('code')->unique(); 
            
            $table->char('customer_id', 16)->charset('binary');
            $table->foreign('customer_id')->references('id')->on('customers');

            $table->char('driver_id', 16)->charset('binary')->nullable();
            $table->foreign('driver_id')->references('id')->on('drivers');

            $table->char('branch_id', 16)->charset('binary');
            $table->foreign('branch_id')->references('id')->on('branches');
            
            $table->enum('delivery_type', ['pickup', 'delivery'])->default('pickup');
            $table->enum('status', ['pending_proof', 'review', 'confirmed', 'dispatched', 'completed', 'cancelled'])->default('pending_proof')->index(); 
            
            $table->timestamp('reservation_expires_at')->nullable()->index(); 
            $table->decimal('total_amount', 12, 2);
            $table->string('proof_of_payment')->nullable(); 
            $table->text('rejection_reason')->nullable(); 
            
            $table->json('delivery_data'); 
            $table->timestamp('reviewed_at')->nullable(); 
            $table->timestamps();
            $table->softDeletes();
        });
    
        Schema::create('order_items', function (Blueprint $table) {
            $table->char('id', 16)->charset('binary')->primary(); // <--- Binario
            
            $table->char('order_id', 16)->charset('binary');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            
            $table->char('sku_id', 16)->charset('binary');
            $table->foreign('sku_id')->references('id')->on('skus');
            
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