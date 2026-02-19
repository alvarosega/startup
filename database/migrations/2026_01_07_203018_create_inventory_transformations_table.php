<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_transformations', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('branch_id');
            $table->foreign('branch_id')->references('id')->on('branches');

            $table->uuid('admin_id');
            $table->foreign('admin_id')->references('id')->on('admins');
            
            $table->uuid('source_sku_id');
            $table->foreign('source_sku_id')->references('id')->on('skus');

            $table->decimal('quantity_removed', 10, 2); 
            
            $table->uuid('destination_sku_id');
            $table->foreign('destination_sku_id')->references('id')->on('skus');

            $table->decimal('quantity_added', 10, 2); 
            
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transformations');
    }
};
