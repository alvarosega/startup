<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('inventory_transformations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('branch_id')->constrained('branches');
            $table->foreignUuid('admin_id')->constrained('admins');
            
            $table->uuid('source_sku_id');
            $table->foreign('source_sku_id')->references('id')->on('skus');
            $table->decimal('quantity_removed', 12, 3); 
            
            $table->uuid('destination_sku_id');
            $table->foreign('destination_sku_id')->references('id')->on('skus');
            $table->decimal('quantity_added', 12, 3); 
            
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('inventory_transformations');
    }
};