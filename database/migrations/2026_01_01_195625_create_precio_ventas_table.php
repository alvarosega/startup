<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::create('prices', function (Blueprint $table) {
            $table->id();
            
            // RELACIONES
            
            // 1. SKU usa UUID (Correcto)
            $table->foreignUuid('sku_id')->constrained('skus')->onDelete('cascade');
            
            // 2. CORRECCIÓN: Branches usa ID Numérico, así que usamos foreignId
            $table->foreignId('branch_id')->nullable()->constrained('branches'); // Null = Precio Nacional
            
            // Datos Económicos
            $table->decimal('list_price', 10, 2); 
            $table->decimal('final_price', 10, 2); 
            $table->integer('min_quantity')->default(1); 
            
            // Vigencia
            $table->timestamp('valid_from')->useCurrent();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prices');
    }
};