<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bundle_items', function (Blueprint $table) {
            // CORRECCIÓN: ID Binario
            $table->char('id', 16)->charset('binary')->primary();
            
            // CORRECCIÓN: Relación con Bundle (Binaria)
            $table->char('bundle_id', 16)->charset('binary');
            $table->foreign('bundle_id')->references('id')->on('bundles')->onDelete('cascade');
            
            // CORRECCIÓN: Relación con SKU (Binaria)
            $table->char('sku_id', 16)->charset('binary');
            $table->foreign('sku_id')->references('id')->on('skus')->onDelete('cascade');
            
            $table->integer('quantity')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bundle_items');
    }
};