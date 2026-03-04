<?php

namespace App\Actions\Driver\Location;

use App\DTOs\Driver\Location\TelemetryDTO;
use App\Models\Driver;
use Illuminate\Support\Facades\Log;

class UpdateTelemetryAction
{
    public function execute(TelemetryDTO $dto): void
    {
        $driver = Driver::find($dto->driverId);
        if (!$driver) return;

        // 1. Corrección de Estado (Aseguramos que el motor sepa que está online)
        if (!$driver->is_online) {
            $driver->update(['is_online' => true]);
        }

        // 2. Persistencia en Base de Datos (Si tienes tabla de historial)
        // \App\Models\DriverLocationLog::create([...]);

        // 3. Intento de actualización en Redis (Silenciado si falla por falta de configuración)
        try {
            if (class_exists(\App\Services\Logistics\RedisLocationService::class)) {
                $redis = app(\App\Services\Logistics\RedisLocationService::class);
                $redis->updateLocation($dto->driverId, $dto->latitude, $dto->longitude);
            }
        } catch (\Throwable $e) {
            Log::warning('Redis no disponible o no configurado en Telemetría: ' . $e->getMessage());
        }

        // 4. Intento de Broadcast WebSocket (Silenciado si falla la clase)
        try {
            if (class_exists(\App\Events\DriverLocationUpdated::class)) {
                broadcast(new \App\Events\DriverLocationUpdated($dto->driverId, $dto->latitude, $dto->longitude))->toOthers();
            }
        } catch (\Throwable $e) {
            Log::warning('Fallo al emitir WebSocket de Telemetría: ' . $e->getMessage());
        }
    }
}