<?php

declare(strict_types=1);

namespace Tests\Feature\Auth\Security;

use App\Models\Operations\Branch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $polygonDefault = 'POLYGON((-68.30 -16.70, -68.00 -16.70, -68.00 -16.30, -68.30 -16.30, -68.30 -16.70))';
    
    Branch::create([
        'id' => (string) Str::uuid(),
        'name' => 'Sucursal Base Default',
        'slug' => 'default',
        'city' => 'La Paz',
        'is_active' => true,
        'is_default' => true,
        'is_operational_paused' => false,
        'location' => DB::raw("ST_SRID(ST_GeomFromText('POINT(-68.18 -16.52)'), 4326)"),
        'coverage_polygon' => DB::raw("ST_SRID(ST_GeomFromText('{$polygonDefault}'), 4326)"),
    ]);
});

test('inyeccion sql y fuzzing en parametros de coordenadas geoespaciales son rechazados por form request', function (mixed $lat, mixed $lng, array|string $expectedErrors): void {
    $response = $this->post(route('customer.register.store'), [
        'phone' => '+59178888888',
        'email' => 'sqli_spatial@test.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'latitude' => $lat,
        'longitude' => $lng,
        'first_name' => 'Hacker',
        'last_name' => 'Spatial',
        'avatar_source' => 'avatar_1.png',
        'address' => 'Zona 0',
    ]);

    $response->assertSessionHasErrors($expectedErrors);
    $this->assertDatabaseCount('customers', 0);
})->with([
    // Inyecciones SQL clásicas en Latitud / Longitud
    'SQLi Drop Table en Lat' => ['-16.50; DROP TABLE branches;--', -68.1500, 'latitude'],
    'SQLi OR 1=1 en Lat' => ["-16.50' OR '1'='1", -68.1500, 'latitude'],
    'SQLi WKT Function en Lng' => [-16.5000, "ST_GeomFromText('POLYGON(...)')", 'longitude'],
    'SQLi Union Select en Lng' => [-16.5000, "-68.15 UNION SELECT null, null, null", 'longitude'],
    
    // Fuzzing de Rango Geodésico WGS84
    'Latitud superior a 90' => [90.0001, -68.1500, 'latitude'],
    'Latitud inferior a -90' => [-90.0001, -68.1500, 'latitude'],
    'Longitud superior a 180' => [-16.5000, 180.0001, 'longitude'],
    'Longitud inferior a -180' => [-16.5000, -180.0001, 'longitude'],
    
    // Tipos de datos corruptos / Fuzzing
    'Latitud y Longitud como cadenas no numericas' => ['invalido_lat', 'invalido_lng', ['latitude', 'longitude']],
    'Coordenadas extremas NaN / Inf' => [999999.99, -999999.99, ['latitude', 'longitude']],
]);