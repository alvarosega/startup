<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // database/migrations/xxxx_xx_xx_create_transfers_table.php

    public function up(): void
    {
        // 1. PRIMERO CREAMOS LA TABLA TRANSFERS
        // (Verificamos si ya existe para evitar errores en ejecuciones manuales raras, aunque el fresh debería borrarla)
        if (!Schema::hasTable('transfers')) {
            Schema::create('transfers', function (Blueprint $table) {
                $table->char('id', 16)->charset('binary')->primary();
                $table->string('code')->unique();
                
                $table->char('origin_branch_id', 16)->charset('binary');
                $table->foreign('origin_branch_id')->references('id')->on('branches');

                $table->char('destination_branch_id', 16)->charset('binary');
                $table->foreign('destination_branch_id')->references('id')->on('branches');

                $table->char('created_by', 16)->charset('binary');
                $table->foreign('created_by')->references('id')->on('admins');

                $table->char('received_by', 16)->charset('binary')->nullable();
                $table->foreign('received_by')->references('id')->on('admins');
                
                $table->string('status')->default('in_transit'); 
                $table->text('notes')->nullable();
                $table->timestamp('shipped_at')->useCurrent();
                $table->timestamp('received_at')->nullable();
                $table->timestamps();
            });
        }

        // 2. CREAMOS LOS ITEMS DE TRANSFERENCIA
        if (!Schema::hasTable('transfer_items')) {
            Schema::create('transfer_items', function (Blueprint $table) {
                $table->char('id', 16)->charset('binary')->primary();
                
                $table->char('transfer_id', 16)->charset('binary');
                $table->foreign('transfer_id')->references('id')->on('transfers')->onDelete('cascade');
                
                $table->char('sku_id', 16)->charset('binary');
                $table->foreign('sku_id')->references('id')->on('skus');

                $table->integer('qty_sent');
                $table->integer('qty_received')->nullable();
                $table->decimal('unit_cost', 10, 2); 
                $table->timestamps();
            });
        }

        // 3. ALTER TABLE INVENTORY LOTS
        // --- CORRECCIÓN CLAVE: Verificar si la columna ya existe ---
        if (Schema::hasTable('inventory_lots') && !Schema::hasColumn('inventory_lots', 'transfer_id')) {
            Schema::table('inventory_lots', function (Blueprint $table) {
                $table->char('transfer_id', 16)->charset('binary')->nullable();
                $table->foreign('transfer_id')->references('id')->on('transfers')->nullOnDelete();
            });
        }
    }
    public function down(): void
    {
        if (Schema::hasColumn('inventory_lots', 'transfer_id')) {
            Schema::table('inventory_lots', function (Blueprint $table) {
                // Laravel a veces necesita el nombre exacto de la foránea para borrarla
                // El nombre default suele ser: tabla_columna_foreign
                $table->dropForeign(['transfer_id']); 
                $table->dropColumn('transfer_id');
            });
        }

        Schema::dropIfExists('transfer_items');
        Schema::dropIfExists('transfers');
    }
};