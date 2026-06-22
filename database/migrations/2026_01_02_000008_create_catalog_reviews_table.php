<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            // CORRECCIÓN: De integer a uuid primary
            $table->uuid('id')->primary()->default(DB::raw('(UUID())'));
            
            $table->foreignUuid('customer_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('product_id')->constrained()->onDelete('cascade');
            
            $table->unsignedTinyInteger('rating'); 
            $table->text('comment')->nullable();
            $table->boolean('is_verified_purchase')->default(false);
            
            $table->unique(['customer_id', 'product_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};