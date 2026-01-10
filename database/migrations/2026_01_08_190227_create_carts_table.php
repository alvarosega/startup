<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->nullable(); // Para invitados (Guests)
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            
            $table->foreignId('branch_id')->constrained('branches'); // La tienda seleccionada
            $table->foreignId('sku_id')->constrained('skus');
            
            $table->integer('quantity')->default(1);
            $table->timestamps();
            
            // Evitar duplicados: Un usuario solo puede tener un registro por SKU en esa sucursal
            $table->unique(['user_id', 'sku_id', 'branch_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
