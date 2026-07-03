<?php

declare(strict_types=1);

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

final class PointCast implements CastsAttributes
{
    /**
     * @return array{lat: float, lng: float}|null
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?array
    {
        if (is_null($value) || strlen($value) < 25) {
            return null;
        }

        $unpacked = unpack('VSRID/CByteOrder/VType/dLongitude/dLatitude', $value);

        if (!$unpacked) {
            return null;
        }

        return [
            'lat' => $unpacked['Latitude'],
            'lng' => $unpacked['Longitude'],
        ];
    }

    /**
     * @param array{lat: float, lng: float}|null $value
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (is_null($value)) {
            return null;
        }

        if (!is_array($value) || !isset($value['lat'], $value['lng'])) {
            throw new \InvalidArgumentException('El formato geoespacial debe contener las claves "lat" y "lng".');
        }

        $lat = (float) $value['lat'];
        $lng = (float) $value['lng'];

        return DB::raw("ST_GeomFromText('POINT({$lng} {$lat})')");
    }
}