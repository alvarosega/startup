<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('bundles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 128);
            $table->string('image_path')->nullable();
            $table->enum('type', ['OFFER', 'TEMPLATE'])->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('bundle_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('bundle_id')->constrained('bundles')->cascadeOnDelete();
            $table->foreignUuid('sku_id')->constrained('skus')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['bundle_id', 'sku_id'], 'idx_bundle_sku_unique');
        });
    }

    public function down(): void {
        Schema::dropIfExists('bundle_items');
        Schema::dropIfExists('bundles');
    }
};