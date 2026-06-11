<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('branch_id')->constrained('branches');
            $table->foreignUuid('sku_id')->constrained('skus');
            $table->foreignUuid('inventory_lot_id')->constrained('inventory_lots');
            $table->foreignUuid('admin_id')->constrained('admins');
            
            $table->string('type', 20); // ENTRY_PURCHASE, OUT_SALE, TRANSFER_OUT, etc.
            $table->decimal('quantity', 12, 3);
            $table->decimal('unit_cost', 12, 2);
            $table->string('reference')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void {
        Schema::dropIfExists('inventory_movements');
    }
};