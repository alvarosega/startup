<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // 1. EL CONTEXTO (Carts)
        Schema::create('carts', function (Blueprint $table) {
            $table->uuid('id')->primary(); // DICTAMEN: Generación en Eloquent, no en DB.
            $table->string('session_id')->nullable()->index();
            
            // Implementación óptima con foreignUuid
            $table->foreignUuid('customer_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignUuid('branch_id')->constrained()->onDelete('cascade');
            
            $table->timestamps();
            $table->softDeletes();

            $table->index(['session_id', 'branch_id']);
        });

        Schema::create('cart_items', function (Blueprint $table) {
            $table->uuid('id')->primary(); // DICTAMEN: Generación en Eloquent, no en DB.
            $table->foreignUuid('cart_id')->constrained()->onDelete('cascade');
            
            // Identificadores (Polimorfismo manual)
            $table->foreignUuid('sku_id')->nullable()->constrained();
            $table->uuid('bundle_id')->nullable()->index()->comment('Polimorfismo suelto hacia bundles');
            
            $table->integer('quantity')->default(1);
            $table->decimal('price_at_addition', 10, 2); // El "Precio Congelado"
            $table->boolean('is_bundle')->default(false)->index();
            
            $table->timestamps();
            
            // Evita duplicados: un carrito tiene un SKU o un Bundle específico
            $table->unique(['cart_id', 'sku_id', 'bundle_id'], 'cart_lookup_unique');
            $table->unique(['cart_id', 'sku_id', 'bundle_id'], 'idx_cart_sku_unique');
        });
        // 2. TABLA DE IDEMPOTENCIA (BLINDAJE DE CONCURRENCIA)
        Schema::create('idempotency_keys', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUIDv7
            $table->string('key')->unique();
            $table->uuid('customer_id')->nullable()->index();
            $table->string('request_path');
            $table->smallInteger('response_code')->nullable();
            $table->longText('response_body')->nullable();
            $table->timestamps();
        });

        // 3. TABLA DE SNAPSHOTS (CONTRATO DE CHECKOUT)
        Schema::create('checkout_snapshots', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUIDv7
            $table->foreignUuid('cart_id')->constrained('carts')->onDelete('cascade');
            $table->foreignUuid('customer_id')->constrained('customers');
            $table->foreignUuid('branch_id')->constrained('branches');
            
            $table->json('logistics_data'); // Snapshot inmutable de cálculos financieros
            $table->timestamp('expires_at')->index();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('checkout_snapshots');
        Schema::dropIfExists('idempotency_keys');
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
    }
};