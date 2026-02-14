<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->char('id', 16)->charset('binary')->primary(); // <--- Binario

            $table->char('branch_id', 16)->charset('binary');
            $table->foreign('branch_id')->references('id')->on('branches');

            $table->char('sku_id', 16)->charset('binary');
            $table->foreign('sku_id')->references('id')->on('skus');

            $table->char('inventory_lot_id', 16)->charset('binary');
            $table->foreign('inventory_lot_id')->references('id')->on('inventory_lots');

            $table->char('admin_id', 16)->charset('binary');
            $table->foreign('admin_id')->references('id')->on('admins');
            
            $table->string('type'); 
            $table->integer('quantity'); 
            $table->decimal('unit_cost', 10, 2); 
            $table->string('reference')->nullable(); 
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};