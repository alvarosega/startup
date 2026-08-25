<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void 
    {
        Schema::create('branch_providers', function (Blueprint $table) {
            $table->foreignUuid('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignUuid('provider_id')->constrained('providers')->cascadeOnDelete();
            
            $table->primary(['branch_id', 'provider_id']);
        });
    }

    public function down(): void 
    {
        Schema::dropIfExists('branch_providers');
    }
};