<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('bundles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('branch_id')->constrained()->onDelete('cascade');
            
            $table->string('name');
            $table->string('slug');
            // Discriminador de Comportamiento
            $table->enum('type', ['atomic', 'template'])->default('atomic')->index();
            
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            
            // Si es 'atomic', este precio es el final. Si es 'template', es null.
            $table->decimal('fixed_price', 10, 2)->nullable();
            
            $table->boolean('is_active')->default(true)->index();
            $table->integer('max_quantity_per_order')->default(5);
            
            // Vigencia
            $table->timestamp('starts_at')->nullable()->index();
            $table->timestamp('ends_at')->nullable()->index();
            
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['branch_id', 'slug']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('bundles');
    }
};