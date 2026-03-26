<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        // I. TABLA DE COMPRAS (CABECERA)
        Schema::create('purchases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('branch_id')->constrained('branches');
            $table->foreignUuid('provider_id')->constrained('providers');
            $table->foreignUuid('admin_id')->constrained('admins');
            $table->string('document_number', 32)->unique();
            $table->date('purchase_date')->index();
            $table->enum('payment_type', ['CASH', 'CREDIT'])->default('CASH');
            $table->decimal('total_amount', 14, 2)->default(0); // Mayor precisión
            $table->string('status')->default('COMPLETED');
            $table->timestamps();
            $table->softDeletes();
        });

        // II. LOTES (VERSIONADOS POR MARIADB)
        Schema::create('inventory_lots', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('purchase_id')->nullable()->constrained('purchases');
            $table->foreignUuid('branch_id')->constrained('branches');
            $table->foreignUuid('sku_id')->constrained('skus');
            $table->string('lot_code', 32)->unique();
            $table->integer('quantity');
            $table->integer('initial_quantity');
            $table->integer('reserved_quantity')->default(0);
            $table->boolean('is_safety_stock')->default(false)->index();
            $table->decimal('unit_cost', 12, 2);
            $table->date('expiration_date')->nullable()->index();
            $table->timestamps();

            $table->index(['branch_id', 'sku_id', 'is_safety_stock'], 'idx_lots_lookup');
        });
        DB::statement('ALTER TABLE inventory_lots ADD SYSTEM VERSIONING');

        // III. BALANCES (SNAPSHOT PARA LECTURA RÁPIDA)
        Schema::create('inventory_balances', function (Blueprint $table) {
            $table->uuid('branch_id');
            $table->uuid('sku_id');
            $table->integer('total_physical')->default(0);
            $table->integer('total_reserved')->default(0);
            $table->integer('total_safety')->default(0);
            $table->timestamps();

            $table->primary(['branch_id', 'sku_id']);
        });

        // IV. KARDEX (MOVIMIENTOS INMUTABLES)
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('branch_id')->constrained('branches');
            $table->foreignUuid('sku_id')->constrained('skus');
            $table->foreignUuid('inventory_lot_id')->constrained('inventory_lots');
            $table->foreignUuid('admin_id')->constrained('admins');
            $table->string('type', 20); // ENTRY_PURCHASE, OUT_SALE, etc.
            $table->integer('quantity');
            $table->decimal('unit_cost', 12, 2);
            $table->string('reference')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });

        // V. BLINDAJE DE RED (IDEMPOTENCIA)
        Schema::create('processed_requests', function (Blueprint $table) {
            $table->string('idempotency_key', 64)->primary();
            $table->timestamp('processed_at')->useCurrent();
        });
    }

    public function down(): void {
        Schema::dropIfExists('processed_requests');
        Schema::dropIfExists('inventory_movements');
        Schema::dropIfExists('inventory_balances');
        Schema::dropIfExists('inventory_lots');
        Schema::dropIfExists('purchases');
    }
};