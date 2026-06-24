<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            // Clave primaria UUIDv7 (se genera desde la aplicación)
            $table->uuid('id')->primary();

            // Polimorfismo para el causante (Super Admin u otro proceso)
            // No usamos foreignId para mantener los logs intactos si el causer desaparece
            $table->string('causer_type');
            $table->uuid('causer_id');

            // Polimorfismo para el usuario afectado (Customer o Driver)
            // Sin integridad referencial física para cumplir con el requisito de permanencia
            $table->string('target_type');
            $table->uuid('target_id');

            // Acción ejecutada (ej: 'customer_activated', 'driver_rejected')
            $table->string('action', 50);

            // Estado de los datos en formato estructurado JSON
            $table->json('payload_before')->nullable();
            $table->json('payload_after')->nullable();

            // Contexto de red e infraestructura
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            // Marca de tiempo de solo inserción (inmutable)
            $table->timestamp('created_at')->useCurrent();

            // Índices compuestos para optimizar consultas de rendimiento en el panel
            $table->index(['causer_type', 'causer_id']);
            $table->index(['target_type', 'target_id']);
            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};