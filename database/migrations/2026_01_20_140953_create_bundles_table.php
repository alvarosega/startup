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
            $table->foreignUuid('branch_id')->constrained('branches')->cascadeOnDelete();
            
            $table->string('name');
            $table->string('slug'); 
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->decimal('fixed_price', 10, 2)->nullable();
        
            // --- NUEVOS CAMPOS PARA TEMPORIZADOR ---
            $table->timestamp('starts_at')->nullable()->index();
            $table->timestamp('ends_at')->nullable()->index();
            
            $table->softDeletes();
            $table->timestamps();
        
            $table->unique(['branch_id', 'slug']); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bundles');
    }
};
