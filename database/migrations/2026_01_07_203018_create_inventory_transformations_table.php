<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_transformations', function (Blueprint $table) {
            $table->char('id', 16)->charset('binary')->primary(); // <--- Binario

            $table->char('branch_id', 16)->charset('binary');
            $table->foreign('branch_id')->references('id')->on('branches');

            $table->char('admin_id', 16)->charset('binary');
            $table->foreign('admin_id')->references('id')->on('admins');
            
            $table->char('source_sku_id', 16)->charset('binary');
            $table->foreign('source_sku_id')->references('id')->on('skus');

            $table->decimal('quantity_removed', 10, 2); 
            
            $table->char('destination_sku_id', 16)->charset('binary');
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