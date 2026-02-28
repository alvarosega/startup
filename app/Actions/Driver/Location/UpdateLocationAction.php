<?php

namespace App\Actions\Driver\Location;

use App\Services\Logistics\RedisLocationService;
use App\Models\Driver;

class UpdateLocationAction
{
    public function __construct(
        private RedisLocationService $redisService
    ) {}

    public function execute(Driver $driver, float $lat, float $lng): void
    {
        // 1. Actualizar en el motor de tiempo real (Redis)
        $this->redisService->updateLocation($driver->id, $lat, $lng);

        // 2. Persistencia en MySQL (Opcional/Asíncrona)
        // Solo guardamos en MySQL cada X minutos para reportes históricos,
        // no para la operación en vivo.
        if (now()->minute % 5 === 0) {
            $driver->locationLogs()->create([
                'latitude' => $lat,
                'longitude' => $lng
            ]);
        }
    }
}