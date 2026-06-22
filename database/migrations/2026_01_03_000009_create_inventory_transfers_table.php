<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('transfers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique();
            $table->foreignUuid('origin_branch_id')->constrained('branches');
            $table->foreignUuid('destination_branch_id')->constrained('branches');
            $table->foreignUuid('created_by')->constrained('admins');
            $table->foreignUuid('received_by')->nullable()->constrained('admins');
            $table->string('status')->default('in_transit'); 
            $table->text('notes')->nullable();
            $table->timestamp('shipped_at')->useCurrent();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
        });

        Schema::create('transfer_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('transfer_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('sku_id')->constrained('skus');
            $table->decimal('qty_sent', 12, 3);
            $table->decimal('qty_received', 12, 3)->nullable();
            // LEY: Columna unit_cost eliminada por definición de negocio
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('transfer_items');
        Schema::dropIfExists('transfers');
    }
};