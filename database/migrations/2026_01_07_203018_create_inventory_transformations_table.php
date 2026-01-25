<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_transformations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignUuid('user_id')->constrained('users'); // CORREGIDO
            
            $table->foreignUuid('source_sku_id')->constrained('skus');
            $table->decimal('quantity_removed', 10, 2); 
            
            $table->foreignUuid('destination_sku_id')->constrained('skus');
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