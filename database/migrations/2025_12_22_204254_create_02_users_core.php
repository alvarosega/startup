<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 20)->unique(); // Login Principal
            $table->string('country_code', 3)->default('BO');
            $table->string('email')->nullable();   // Opcional
            $table->string('password')->nullable();
            $table->integer('trust_score')->default(50);
            $table->boolean('is_active')->default(true);
            $table->foreignId('current_level_id')->nullable()->constrained('levels');
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('avatar_type')->default('icon'); 
            $table->string('avatar_source')->default('avatar_1.svg');
            $table->rememberToken();
            $table->timestamps();
        });

        // ELIMINADO: model_has_roles y model_has_permissions.
        // La migración oficial de Spatie (2026_...) se encargará de esto.
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};