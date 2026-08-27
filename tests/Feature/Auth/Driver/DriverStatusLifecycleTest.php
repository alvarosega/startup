<?php

declare(strict_types=1);

use App\Models\Operations\Branch;
use App\Models\Users\Driver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $polygonWkt = 'POLYGON((-68.30 -16.70, -68.00 -16.70, -68.00 -16.30, -68.30 -16.30, -68.30 -16.70))';
    $this->branch = Branch::create([
        'id'                    => (string) Str::uuid(),
        'name'                  => 'Sucursal Operativa',
        'slug'                  => 'operativa',
        'city'                  => 'La Paz',
        'is_active'             => true,
        'is_default'            => true,
        'is_operational_paused' => false,
        'location'              => DB::raw("ST_SRID(ST_GeomFromText('POINT(-68.18 -16.52)'), 4326)"),
        'coverage_polygon'      => DB::raw("ST_SRID(ST_GeomFromText('{$polygonWkt}'), 4326)"),
    ]);
});

test('chofer en estado pending no puede colocarse en linea ni disponible', function (): void {
    $driver = Driver::create([
        'id'           => (string) Str::uuid(),
        'branch_id'    => $this->branch->id,
        'phone'        => '+59174455667',
        'email'        => 'driver_not_approved@test.com',
        'password'     => 'Password123!',
        'status'       => 'pending',
        'is_online'    => false,
        'is_available' => false,
    ]);

    $this->actingAs($driver, 'driver');

    $this->post(route('driver.status.toggle'));

    expect($driver->fresh()->is_online)->toBeFalse();
    expect($driver->fresh()->is_available)->toBeFalse();
});

test('chofer en estado approved puede alternar su disponibilidad operativa', function (): void {
    $driver = Driver::create([
        'id'           => (string) Str::uuid(),
        'branch_id'    => $this->branch->id,
        'phone'        => '+59175566778',
        'email'        => 'driver_active_ops@test.com',
        'password'     => 'Password123!',
        'status'       => 'approved',
        'is_online'    => false,
        'is_available' => false,
    ]);

    $this->actingAs($driver, 'driver');

    $response = $this->post(route('driver.status.toggle'));

    $response->assertSessionHasNoErrors();
    $response->assertRedirect();
    expect($driver->fresh()->is_online)->toBeTrue();
    expect($driver->fresh()->is_available)->toBeTrue();
});