<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('removal_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('removal_request_id')->constrained('removal_requests')->cascadeOnDelete();
            $table->foreignUuid('inventory_lot_id')->constrained('inventory_lots')->restrictOnDelete();
            
            $table->decimal('quantity', 12, 3);
            $table->decimal('unit_cost', 12, 2);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('removal_items');
    }
};