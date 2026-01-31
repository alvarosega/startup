<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::create('prices', function (Blueprint $table) {
            $table->id();
            
            // Relaciones
            $table->foreignUuid('sku_id')->constrained('skus')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches'); // Null = Nacional
            
            // Clasificación
            // Ej: 'regular', 'offer', 'clearance'
            $table->string('type')->default('regular')->index(); 
            
            // Datos Económicos
            $table->decimal('list_price', 10, 2); // Precio "tachado" (Precio de lista original)
            $table->decimal('final_price', 10, 2); // Precio real de venta
            
            // Reglas de aplicación
            $table->integer('min_quantity')->default(1); // Para precios mayoristas
            $table->integer('priority')->default(0); // Si hay 2 ofertas, gana la de mayor prioridad
            
            // Vigencia
            $table->timestamp('valid_from')->useCurrent();
            $table->timestamp('valid_to')->nullable(); // CRÍTICO: Para ofertas temporales
            
            $table->timestamps();
            $table->softDeletes();

            // Índices para velocidad (Búsqueda de precio actual es pesada)
            $table->index(['sku_id', 'branch_id', 'valid_from', 'valid_to']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prices');
    }
};