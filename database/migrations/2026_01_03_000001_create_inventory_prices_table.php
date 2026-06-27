<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('prices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sku_id')->constrained('skus')->cascadeOnDelete();
            $table->foreignUuid('branch_id')->constrained('branches')->cascadeOnDelete(); 
            
            // RECTIFICACIÓN: Indexación explícita para tipificación comercial cerrada
            $table->string('type')->index(); 
            $table->decimal('list_price', 12, 2)->default(0.00); 
            $table->decimal('final_price', 12, 2)->default(0.00); 
            
            $table->integer('min_quantity')->default(1); 
            $table->integer('priority')->default(1); 
            $table->unsignedBigInteger('deleted_epoch')->default(0);
            
            $table->timestamp('valid_from')->useCurrent();
            $table->timestamp('valid_to')->nullable(); 

            $table->foreignUuid('created_by_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->foreignUuid('updated_by_id')->nullable()->constrained('admins')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        
            $table->unique(
                ['branch_id', 'sku_id', 'type', 'min_quantity', 'priority', 'deleted_epoch'], 
                'idx_prices_operational_unique'
            );

            $table->index(
                ['branch_id', 'sku_id', 'priority', 'valid_from', 'valid_to', 'deleted_epoch'], 
                'idx_price_winning_lookup'
            );
        });
    }

    public function down(): void { 
        Schema::dropIfExists('prices'); 
    }
};