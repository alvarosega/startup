<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        Schema::create('inventory_lots', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('purchase_id')->nullable()->constrained('purchases');
            $table->foreignUuid('transfer_id')->nullable()->constrained('transfers')->nullOnDelete();
            $table->foreignUuid('branch_id')->constrained('branches');
            $table->foreignUuid('sku_id')->constrained('skus');
            
            $table->string('lot_code', 32)->unique();
            $table->decimal('quantity', 12, 3);
            $table->decimal('initial_quantity', 12, 3);
            $table->decimal('reserved_quantity', 12, 3)->default(0);
            
            $table->boolean('is_safety_stock')->default(false)->index();
            $table->decimal('unit_cost', 12, 2);
            $table->date('expiration_date')->nullable()->index();
            $table->timestamps();

            $table->index(['branch_id', 'sku_id', 'is_safety_stock'], 'idx_lots_lookup');
        });

        DB::statement('ALTER TABLE inventory_lots ADD SYSTEM VERSIONING');
    }

    public function down(): void {
        DB::statement('ALTER TABLE inventory_lots DROP SYSTEM VERSIONING');
        Schema::dropIfExists('inventory_lots');
    }
};