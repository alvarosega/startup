<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    
    public function up(): void 
    {
        // 1. Crear Tabla Branches
        Schema::create('branches', function (Blueprint $table) {
            $table->id(); // Entero (Compatible con las relaciones 'foreignId' de otras tablas)
            
            $table->string('name')->unique(); 
            $table->string('city')->default('La Paz');
            $table->string('phone', 20)->nullable();
            $table->string('address')->nullable();
            
            // Geolocalización
            $table->decimal('latitude', 10, 8)->nullable(); 
            $table->decimal('longitude', 11, 8)->nullable();

            // Polígonos y Horarios
            $table->json('coverage_polygon')->nullable();
            $table->json('opening_hours')->nullable();
            
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            $table->softDeletes(); // <--- CRÍTICO: Soluciona el error SQLSTATE[42S22]
        });

        // 2. Modificar tablas existentes (Relaciones Inversas)
        
        // Users: Asignar usuario a una sucursal (Staff)
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'branch_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('branch_id')
                      ->nullable()
                      ->after('id')
                      ->constrained('branches')
                      ->nullOnDelete();
            });
        }

        // Verificaciones: Dónde se verificó
        if (Schema::hasTable('user_verifications') && !Schema::hasColumn('user_verifications', 'branch_id')) {
            Schema::table('user_verifications', function (Blueprint $table) {
                $table->foreignId('branch_id')
                      ->nullable()
                      ->after('user_id')
                      ->constrained('branches')
                      ->nullOnDelete();
            });
        }

        // Direcciones: Asignación logística (opcional)
        if (Schema::hasTable('user_addresses') && !Schema::hasColumn('user_addresses', 'branch_id')) {
            Schema::table('user_addresses', function (Blueprint $table) {
                $table->foreignId('branch_id')
                      ->nullable()
                      ->constrained('branches')
                      ->nullOnDelete();
            });
        }
    }

    public function down(): void 
    {
        // 1. Eliminar claves foráneas primero
        if (Schema::hasColumn('user_addresses', 'branch_id')) {
            Schema::table('user_addresses', function (Blueprint $table) {
                $table->dropForeign(['branch_id']);
                $table->dropColumn('branch_id');
            });
        }

        if (Schema::hasColumn('user_verifications', 'branch_id')) {
            Schema::table('user_verifications', function (Blueprint $table) {
                $table->dropForeign(['branch_id']);
                $table->dropColumn('branch_id');
            });
        }

        if (Schema::hasColumn('users', 'branch_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['branch_id']);
                $table->dropColumn('branch_id');
            });
        }

        // 2. Eliminar tabla
        Schema::dropIfExists('branches');
    }
};