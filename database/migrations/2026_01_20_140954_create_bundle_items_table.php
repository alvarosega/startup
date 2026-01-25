<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bundle_items', function (Blueprint $table) {
            $table->id(); // ID interno del item (puede ser INT)
            
            // CORRECCIÓN: Como cambiamos Bundles a UUID, esto funciona perfecto ahora
            $table->foreignUuid('bundle_id')->constrained('bundles')->onDelete('cascade');
            
            // SKU ya era UUID desde el paso anterior
            $table->foreignUuid('sku_id')->constrained('skus')->onDelete('cascade'); 
            
            $table->integer('quantity')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bundle_items');
    }
};