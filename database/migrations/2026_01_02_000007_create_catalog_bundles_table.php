<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // Entidad Maestra del Combo Administrativo
        Schema::create('bundles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 128);
            $table->string('image_path')->nullable();
            $table->enum('type', ['OFFER', 'TEMPLATE'])->default('OFFER')->index();
            
            // Control cronológico automatizado para combos estacionales / promocionales
            $table->timestamp('starts_at')->nullable()->index();
            $table->timestamp('ends_at')->nullable()->index();
            
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        // Desglose Estructural: Componentes del Combo
        Schema::create('bundle_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('bundle_id')->constrained('bundles')->cascadeOnDelete();
            
            // Restricción dura: Prohibido borrar un SKU si forma parte de una agrupación operativa
            $table->foreignUuid('sku_id')->constrained('skus')->restrictOnDelete();
            
            // Volumen fraccionable o unitario del componente dentro del paquete
            $table->decimal('quantity', 12, 3)->default(1.000);
            $table->timestamps();

            // Restricción de integridad: Impide duplicar el mismo artículo en el mismo combo
            $table->unique(['bundle_id', 'sku_id'], 'idx_bundle_sku_unique');
        });
    }

    public function down(): void {
        Schema::dropIfExists('bundle_items');
        Schema::dropIfExists('bundles');
    }
};