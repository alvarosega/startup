<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Sesiones
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            
            // --- CORRECCIÓN DEFINITIVA ---
            // Usamos string con charset 'binary'. Esto crea un VARCHAR BINARY o VARBINARY en MySQL.
            // Es indexable y acepta tus datos binarios crudos sin errores de UTF-8.
            // Longitud 64 es suficiente (tus UUIDs binarios ocupan 16 bytes).
            $table->string('user_id', 64)->charset('binary')->nullable()->index();

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // 2. Password Reset
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // 3. Notificaciones
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->uuidMorphs('notifiable'); 
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};