<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bundle_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            $table->uuid('bundle_id');
            $table->foreign('bundle_id')->references('id')->on('bundles')->onDelete('cascade');
            
            $table->uuid('sku_id');
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
