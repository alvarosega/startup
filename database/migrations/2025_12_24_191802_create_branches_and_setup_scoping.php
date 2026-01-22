<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); 
            $table->string('city')->default('La Paz');
            $table->string('phone', 20)->nullable();
            $table->string('address')->nullable();
            
            // REMOVED '->after(...)'. Order is defined by line position.
            $table->decimal('latitude', 10, 8)->nullable(); 
            $table->decimal('longitude', 11, 8)->nullable();

            $table->json('coverage_polygon')->nullable();
            $table->json('opening_hours')->nullable();
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Relations
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('branch_id')->after('id')->nullable()->constrained('branches')->onDelete('set null');
        });

        Schema::table('user_verifications', function (Blueprint $table) {
            $table->foreignId('branch_id')->after('user_id')->nullable()->constrained('branches')->onDelete('set null');
        });
    }

    public function down(): void {
        Schema::table('user_verifications', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });
        Schema::dropIfExists('branches');
    }
};