<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::create('prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sku_id')->constrained('skus')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches'); // Null = Precio Base Nacional
            
            $table->decimal('list_price', 10, 2); // Precio de Lista (antes de descuentos)
            $table->decimal('final_price', 10, 2); // Precio real de venta
            $table->integer('min_quantity')->default(1); // Para escalas de precios
            
            $table->timestamp('valid_from')->useCurrent();
            $table->timestamp('valid_to')->nullable(); // Null = Vigente
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prices');
    }
};