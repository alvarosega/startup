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
            $table->foreignUuid('sku_id')->constrained('skus');
            $table->foreignId('inventory_lot_id')->constrained('inventory_lots');
            $table->foreignUuid('user_id')->constrained('users'); // CORREGIDO
            
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