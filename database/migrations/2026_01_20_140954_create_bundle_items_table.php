<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bundle_items', function (Blueprint $table) {
            // ELIMINAR: $table->uuid('id')->primary();
            
            $table->foreignUuid('bundle_id')->constrained('bundles')->cascadeOnDelete();
            $table->foreignUuid('sku_id')->constrained('skus')->cascadeOnDelete();
            $table->integer('quantity')->default(1);
            $table->timestamps();
    
            // LLAVE PRIMARIA COMPUESTA (Performance Senior)
            $table->primary(['bundle_id', 'sku_id']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('bundle_items');
    }
};
