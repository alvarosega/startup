<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('ad_campaigns', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // RECTIFICACIÓN: Nullable para dar soporte limpio a campañas de tipo 'INTERNAL'
            $table->foreignUuid('provider_id')->nullable()->constrained('providers')->restrictOnDelete(); 
            
            $table->string('name', 128);
            $table->enum('type', ['PAID', 'INTERNAL'])->default('PAID')->index();
            
            $table->timestamp('starts_at')->nullable()->index();
            $table->timestamp('ends_at')->nullable()->index();
            $table->boolean('is_active')->default(true)->index();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void { 
        Schema::dropIfExists('ad_campaigns'); 
    }
};