<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bundles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            $table->uuid('branch_id');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            
            $table->string('name');
            $table->string('slug'); 
            
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->decimal('fixed_price', 10, 2)->nullable();
            
            $table->softDeletes();
            $table->timestamps();

            $table->index(['branch_id', 'is_active']);
            $table->unique(['branch_id', 'slug']); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bundles');
    }
};
