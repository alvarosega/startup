<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('removal_requests', function (Blueprint $table) {
            $table->char('id', 16)->charset('binary')->primary(); // <--- Binario
            $table->string('code')->unique(); 
            
            $table->char('branch_id', 16)->charset('binary');
            $table->foreign('branch_id')->references('id')->on('branches');
            
            $table->char('admin_id', 16)->charset('binary'); 
            $table->foreign('admin_id')->references('id')->on('admins');

            $table->char('approved_by', 16)->charset('binary')->nullable(); 
            $table->foreign('approved_by')->references('id')->on('admins');
            
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->enum('reason', ['expiration', 'damage', 'theft', 'internal_use', 'admin_error']);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('removal_items', function (Blueprint $table) {
            $table->char('id', 16)->charset('binary')->primary(); // <--- Binario
            
            $table->char('removal_request_id', 16)->charset('binary');
            $table->foreign('removal_request_id')->references('id')->on('removal_requests')->onDelete('cascade');
            
            $table->char('inventory_lot_id', 16)->charset('binary');
            $table->foreign('inventory_lot_id')->references('id')->on('inventory_lots');
            
            $table->decimal('quantity', 10, 2); 
            $table->decimal('unit_cost', 10, 2); 
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('removal_items');
        Schema::dropIfExists('removal_requests');
    }
};