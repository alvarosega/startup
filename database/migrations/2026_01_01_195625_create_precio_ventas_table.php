<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::create('prices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            $table->uuid('sku_id');
            $table->foreign('sku_id')->references('id')->on('skus')->onDelete('cascade');

            $table->uuid('branch_id')->nullable();
            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete(); 
            
            $table->string('type')->default('regular')->index(); 
            $table->decimal('list_price', 10, 2); 
            $table->decimal('final_price', 10, 2); 
            $table->integer('min_quantity')->default(1); 
            $table->integer('priority')->default(0); 
            $table->timestamp('valid_from')->useCurrent();
            $table->timestamp('valid_to')->nullable(); 
            
            $table->timestamps();
            $table->softDeletes();

            $table->index(['sku_id', 'branch_id', 'valid_from', 'valid_to']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prices');
    }
};