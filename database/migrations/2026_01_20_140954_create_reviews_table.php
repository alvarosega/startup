<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            
            // Usuario es UUID
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            
            // CORRECCIÓN CRÍTICA:
            // Como Products y Bundles ahora son UUIDs, 
            // no podemos usar 'morphs()' (que crea BIGINT).
            // Debemos usar 'uuidMorphs()' (que crea CHAR 36).
            $table->uuidMorphs('reviewable'); 
            
            $table->unsignedTinyInteger('rating'); 
            $table->text('comment')->nullable();
            $table->boolean('is_verified_purchase')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};