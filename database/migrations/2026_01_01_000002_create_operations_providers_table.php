<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('providers', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUIDv7 desde la aplicación
            $table->string('company_name'); 
            $table->string('commercial_name')->nullable();
            $table->string('slug');
            $table->string('tax_id'); 
            $table->string('internal_code')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('email_orders')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->integer('lead_time_days')->default(1); 
            $table->decimal('min_order_value', 12, 2)->default(0); 
            $table->integer('credit_days')->default(0); 
            $table->decimal('credit_limit', 12, 2)->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->text('notes')->nullable();
            
            $table->unsignedBigInteger('deleted_epoch')->default(0); // Columna inyectada obligatoria para unicidad
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['slug', 'deleted_epoch'], 'idx_providers_slug_unique');
            $table->unique(['tax_id', 'deleted_epoch'], 'idx_providers_tax_unique');
            $table->unique(['internal_code', 'deleted_epoch'], 'idx_providers_code_unique');
            $table->index(['is_active', 'id']);
        });
    }
    public function down(): void { Schema::dropIfExists('providers'); }
};