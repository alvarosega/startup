<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // IMPORTANTE

return new class extends Migration {
    public function up(): void {
        // 1. CREACIÓN DE TABLAS DE SOPORTE (TRANSFERS Y ITEMS)
        if (!Schema::hasTable('transfers')) {
            Schema::create('transfers', function (Blueprint $table) {
                $table->uuid('id')->primary()->default(DB::raw('(UUID())'));
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
        }

        if (!Schema::hasTable('transfer_items')) {
            Schema::create('transfer_items', function (Blueprint $table) {
                $table->uuid('id')->primary()->default(DB::raw('(UUID())'));
                $table->foreignUuid('transfer_id')->constrained()->onDelete('cascade');
                $table->foreignUuid('sku_id')->constrained('skus');
                $table->integer('qty_sent');
                $table->integer('qty_received')->nullable();
                $table->decimal('unit_cost', 12, 2); 
                $table->timestamps();
            });
        }

        // 2. ALTERACIÓN DE TABLA VERSIONADA (CRÍTICO)
        if (Schema::hasTable('inventory_lots') && !Schema::hasColumn('inventory_lots', 'transfer_id')) {
            // PROTOCOLO DE BYPASS PARA TABLAS VERSIONADAS
            DB::statement('SET SESSION system_versioning_alter_history = 1');

            Schema::table('inventory_lots', function (Blueprint $table) {
                $table->uuid('transfer_id')->nullable()->after('purchase_id');
                $table->foreign('transfer_id')->references('id')->on('transfers')->nullOnDelete();
            });
        }
    }

    public function down(): void {
        if (Schema::hasColumn('inventory_lots', 'transfer_id')) {
            DB::statement('SET SESSION system_versioning_alter_history = 1');
            Schema::table('inventory_lots', function (Blueprint $table) {
                $table->dropForeign(['transfer_id']); 
                $table->dropColumn('transfer_id');
            });
        }
        Schema::dropIfExists('transfer_items');
        Schema::dropIfExists('transfers');
    }
};
