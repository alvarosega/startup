<?php

declare(strict_types=1);

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PointCast implements CastsAttributes
{
    /**
     * Desempaqueta el formato binario interno de MySQL Geometry (SRID 4 bytes + WKB)
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?array
    {
        if (is_null($value) || strlen($value) < 25) {
            return null;
        }

        // Estructura MySQL: 4 bytes SRID, 1 byte Endian, 4 bytes Tipo (1 = Point), 8 bytes X, 8 bytes Y
        $unpacked = unpack('VSRID/CByteOrder/VType/dLongitude/dLatitude', $value);

        if (!$unpacked) {
            return null;
        }

        return [
            'latitude'  => $unpacked['Latitude'],
            'longitude' => $unpacked['Longitude'],
        ];
    }

    /**
     * Serializa un array de coordenadas a una expresión espacial nativa de MySQL
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (is_null($value)) {
            return null;
        }

        if (!is_array($value) || !isset($value['latitude'], $value['longitude'])) {
            throw new \InvalidArgumentException('El formato geospacial debe contener "latitude" y "longitude".');
        }

        return DB::raw("ST_GeomFromText('POINT({$value['longitude']} {$value['latitude']})')");
    }
}