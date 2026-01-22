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
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('origin_branch_id')->constrained('branches');
            $table->foreignId('destination_branch_id')->constrained('branches');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('received_by')->nullable()->constrained('users');
            $table->string('status')->default('in_transit'); 
            $table->text('notes')->nullable();
            $table->timestamp('shipped_at')->useCurrent();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
        });

        // 2. CREAMOS LOS ITEMS DE TRANSFERENCIA
        Schema::create('transfer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transfer_id')->constrained('transfers')->onDelete('cascade');
            $table->foreignId('sku_id')->constrained('skus');
            $table->integer('qty_sent');
            $table->integer('qty_received')->nullable();
            $table->decimal('unit_cost', 10, 2); 
            $table->timestamps();
        });

        // 3. --- EL TRUCO MAESTRO ---
        // AHORA que la tabla 'transfers' YA EXISTE, volvemos a 'inventory_lots' 
        // y le agregamos la restricción de llave foránea.
        Schema::table('inventory_lots', function (Blueprint $table) {
            $table->foreign('transfer_id')
                ->references('id')
                ->on('transfers')
                ->nullOnDelete(); // O cascade, según prefieras
        });
    }

    public function down(): void
    {
        // Al revertir, primero debemos soltar la llave foránea para evitar errores
        Schema::table('inventory_lots', function (Blueprint $table) {
            $table->dropForeign(['transfer_id']);
        });

        Schema::dropIfExists('transfer_items');
        Schema::dropIfExists('transfers');
    }
};