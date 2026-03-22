<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('ad_placements', function (Blueprint $table) {
            $table->uuid('id')->primary(); 
            $table->string('name');
            $table->string('code')->unique(); // HOME_HERO, SEARCH_TOP, etc.
            $table->integer('max_items')->default(5);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('ad_placements'); }
};