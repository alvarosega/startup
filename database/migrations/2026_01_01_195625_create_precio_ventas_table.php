<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('prices', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('(UUID())'));
            
            // LA LEY: Relaciones obligatorias y en cascada
            $table->foreignUuid('sku_id')->constrained('skus')->cascadeOnDelete();
            $table->foreignUuid('branch_id')->constrained('branches')->cascadeOnDelete(); 
            
            // Tipo de precio: regular, offer, member, wholesale, liquidation, staff
            $table->string('type')->index(); 
            
            // Escala Financiera: list_price (tachado) vs final_price (cobrado)
            $table->decimal('list_price', 12, 2)->default(0); 
            $table->decimal('final_price', 12, 2); 
            
            // Reglas de Negocio
            $table->integer('min_quantity')->default(1); 
            $table->integer('priority')->default(1)->index(); // 1 a 6
            
            $table->timestamp('valid_from')->useCurrent();
            $table->timestamp('valid_to')->nullable(); 

            // Auditoría Zero-Trust
            $table->foreignUuid('created_by_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->foreignUuid('updated_by_id')->nullable()->constrained('admins')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        
            // Índice de Cobertura para el Motor de Resolución de Precios
            $table->index(['sku_id', 'branch_id', 'priority', 'valid_from', 'valid_to'], 'idx_price_winning_lookup');
        });
    }

    public function down(): void { Schema::dropIfExists('prices'); }
};