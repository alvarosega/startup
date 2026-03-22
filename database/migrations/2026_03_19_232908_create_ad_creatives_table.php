<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ad_creatives', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('campaign_id')->constrained('ad_campaigns')->cascadeOnDelete();
            $table->foreignUuid('placement_id')->constrained('ad_placements')->cascadeOnDelete();
            $table->foreignUuid('branch_id')->constrained('branches')->cascadeOnDelete();
    
            // OPCIÓN A: Anclaje a Categoría (Nullable para permitir banners de Home)
            $table->foreignUuid('category_id')->nullable()->constrained('categories')->nullOnDelete();
    
            $table->uuidMorphs('target'); 
            $table->string('name');
            $table->string('image_mobile_path');
            $table->string('image_desktop_path');
            $table->enum('action_type', ['ADD_TO_CART', 'NAVIGATE'])->default('ADD_TO_CART');
            $table->integer('sort_order')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_creatives');
    }
};