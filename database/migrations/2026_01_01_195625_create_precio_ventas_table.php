<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::create('prices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sku_id')->constrained('skus')->onDelete('cascade');
            $table->foreignUuid('branch_id')->nullable()->constrained('branches')->nullOnDelete(); 
            $table->string('type')->default('regular'); 
            $table->decimal('list_price', 12, 2)->default(0); 
            $table->decimal('final_price', 12, 2); 
            $table->integer('min_quantity')->default(1); 
            $table->integer('priority')->default(0); 
            $table->timestamp('valid_from')->useCurrent();
            $table->timestamp('valid_to')->nullable(); 

            // CAMPOS DE AUDITORÍA
            $table->foreignUuid('created_by_id')->nullable()->constrained('admins');
            $table->foreignUuid('updated_by_id')->nullable()->constrained('admins');

            $table->timestamps();
            $table->softDeletes();
        
            $table->index(['sku_id', 'branch_id', 'valid_from', 'valid_to', 'priority'], 'idx_price_branch_lookup');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('prices');
    }
};