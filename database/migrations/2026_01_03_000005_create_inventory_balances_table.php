<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        Schema::create('inventory_balances', function (Blueprint $table) {
            $table->foreignUuid('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignUuid('sku_id')->constrained('skus')->cascadeOnDelete();
            
            $table->decimal('total_physical', 12, 3)->default(0.000);
            $table->decimal('total_reserved', 12, 3)->default(0.000);
            $table->decimal('total_safety', 12, 3)->default(0.000);
            $table->decimal('total_quarantine', 12, 3)->default(0.000);
            $table->timestamps();

            $table->primary(['branch_id', 'sku_id']);
        });

        DB::statement('ALTER TABLE inventory_balances ADD CONSTRAINT chk_balances_available_positive CHECK (total_physical >= (total_reserved + total_quarantine + total_safety))');
    }

    public function down(): void {
        Schema::dropIfExists('inventory_balances');
    }
};