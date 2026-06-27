<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('inventory_lots', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('purchase_id')->nullable()->constrained('purchases')->restrictOnDelete();
            $table->foreignUuid('transfer_id')->nullable()->index(); 
            $table->foreignUuid('branch_id')->constrained('branches')->restrictOnDelete();
            $table->foreignUuid('sku_id')->constrained('skus')->restrictOnDelete();
            
            $table->string('lot_code', 32);
            
            $table->decimal('quantity', 12, 3); 
            $table->decimal('initial_quantity', 12, 3); 
            
            $table->decimal('safety_quantity', 12, 3)->default(0.000); 
            $table->decimal('initial_safety_quantity', 12, 3)->default(0.000); 
            
            $table->decimal('reserved_quantity', 12, 3)->default(0.000); 
            $table->decimal('cost_price', 12, 2)->default(0.00);
            
            $table->boolean('is_quarantine')->default(false)->index(); 
            $table->date('expiration_date')->nullable();
            
            $table->unsignedBigInteger('deleted_epoch')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(
                ['branch_id', 'sku_id', 'is_quarantine', 'expiration_date'], 
                'idx_lots_fefo_lookup'
            );

            $table->unique(['branch_id', 'lot_code', 'deleted_epoch'], 'idx_lots_branch_code_unique');
        });
    }

    public function down(): void {
        Schema::dropIfExists('inventory_lots');
    }
};