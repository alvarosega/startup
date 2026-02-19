<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('removal_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique(); 
            
            $table->uuid('branch_id');
            $table->foreign('branch_id')->references('id')->on('branches');
            
            $table->uuid('admin_id'); 
            $table->foreign('admin_id')->references('id')->on('admins');

            $table->uuid('approved_by')->nullable(); 
            $table->foreign('approved_by')->references('id')->on('admins');
            
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->enum('reason', ['expiration', 'damage', 'theft', 'internal_use', 'admin_error']);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('removal_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            $table->uuid('removal_request_id');
            $table->foreign('removal_request_id')->references('id')->on('removal_requests')->onDelete('cascade');
            
            $table->uuid('inventory_lot_id');
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
