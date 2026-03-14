<?php

namespace App\Actions\Driver\Location;

use App\DTOs\Driver\Location\TelemetryDTO;
use App\Services\Logistics\RedisLocationService;
use App\Models\Driver;
use Illuminate\Support\Facades\Log;

class UpdateTelemetryAction
{
    public function __construct(
        private RedisLocationService $redisService
    ) {}

    public function execute(TelemetryDTO $dto): void
    {
        // 1. Actualización Atómica en Memoria (Redis) - ALTA PRIORIDAD
        // Esto alimenta el mapa del cliente y el monitor del admin en tiempo real.
        $this->redisService->updateLocation($dto->driverId, $dto->latitude, $dto->longitude);

        // 2. Persistencia Inteligente (MySQL) - BAJA PRIORIDAD
        // Solo guardamos rastro histórico cada 5 minutos para no matar el rendimiento.
        if (now()->minute % 5 === 0) {
            // Usamos un modelo de log dedicado para no inflar la tabla 'drivers'
            \App\Models\DriverLocationLog::create([
                'driver_id' => $dto->driverId,
                'latitude'  => $dto->latitude,
                'longitude' => $dto->longitude,
                'created_at' => now(),
            ]);
        }

        // 3. Notificación de proximidad (WebSockets)
        // Solo si tienes implementado Laravel Reverb o Pusher.
        try {
            broadcast(new \App\Events\DriverLocationUpdated($dto->driverId, $dto->latitude, $dto->longitude))->toOthers();
        } catch (\Throwable $e) {
            // Silenciamos para no interrumpir el flujo del conductor por un fallo de socket
        }
    }
}