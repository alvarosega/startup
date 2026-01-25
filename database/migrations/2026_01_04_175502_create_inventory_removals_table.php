<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_removals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignUuid('sku_id')->constrained('skus');
            $table->foreignUuid('user_id')->constrained('users'); // CORREGIDO (Solicitante)
            $table->foreignUuid('approved_by')->nullable()->constrained('users'); // CORREGIDO (Aprobador)
            
            $table->integer('cantidad');
            $table->string('motivo'); 
            $table->text('observaciones')->nullable();
            $table->string('evidencia_url')->nullable(); 
            
            $table->string('estado')->default('pendiente');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_removals');
    }
};