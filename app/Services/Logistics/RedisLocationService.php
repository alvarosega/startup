<?php

namespace App\Services\Logistics;

use Illuminate\Support\Facades\Redis;

class RedisLocationService
{
    private const GEO_KEY = 'drivers:locations';
    private const STATUS_PREFIX = 'driver:';

    /**
     * Actualiza la posición y el estado atómicamente.
     */
    public function updateLocation(string $driverId, float $lat, float $lng): array
    {
        $statusKey = self::STATUS_PREFIX . $driverId . ':status';

        // Ejecución del Script Lua para garantizar atomicidad
        return Redis::eval(
            $this->getLuaScript(),
            2, // Número de llaves
            self::GEO_KEY,
            $statusKey,
            $driverId,
            $lng,
            $lat,
            now()->timestamp
        );
    }

    /**
     * Busca conductores disponibles en un radio de X km.
     */
    public function findNearbyDrivers(float $lat, float $lng, int $radiusKm = 5): array
    {
        // GEOSEARCH es compatible con Redis 6.2+. 
        // Si usas versiones anteriores, usa GEORADIUS.
        return Redis::geosearch(
            self::GEO_KEY,
            'FROMLONLAT', $lng, $lat,
            'BYRADIUS', $radiusKm, 'km',
            'WITHDIST', 'ASC'
        );
    }

    private function getLuaScript(): string
    {
        return <<<'LUA'
            redis.call('GEOADD', KEYS[1], ARGV[2], ARGV[3], ARGV[1])
            redis.call('HSET', KEYS[2], 'lat', ARGV[3], 'lng', ARGV[2], 'last_seen', ARGV[4], 'is_online', '1')
            return redis.call('HGETALL', KEYS[2])
LUA;
    }
}