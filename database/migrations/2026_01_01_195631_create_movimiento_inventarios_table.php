<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('sku_id')->constrained('skus');
            $table->foreignId('inventory_lot_id')->constrained('inventory_lots');
            $table->foreignId('user_id')->constrained('users');
            
            $table->string('type'); // purchase, sale, adjustment_in, adjustment_out, transfer_out, transfer_in
            $table->integer('quantity'); // Positivo o Negativo
            $table->decimal('unit_cost', 10, 2); // Costo en el momento del movimiento
            $table->string('reference')->nullable(); // "Venta #123", "Merma #5"
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};