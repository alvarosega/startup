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
            $table->foreignId('sku_id')->constrained('skus')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches'); // Null = Precio Nacional
            
            // Datos Económicos
            $table->decimal('list_price', 10, 2); 
            $table->decimal('final_price', 10, 2); 
            $table->integer('min_quantity')->default(1); 
            
            // Vigencia
            $table->timestamp('valid_from')->useCurrent();
            // YA NO USAMOS valid_to, usamos el deleted_at para cerrar vigencia
            
            $table->timestamps();
            $table->softDeletes(); // <--- ESTA ES LA COLUMNA QUE FALTA
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prices');
    }
};