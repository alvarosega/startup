<?php

declare(strict_types=1);

use App\Models\Users\Customer;
use App\Models\Operations\Branch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $polygonWkt = 'POLYGON((-68.20 -16.60, -68.05 -16.60, -68.05 -16.45, -68.20 -16.45, -68.20 -16.60))';
    $pointWkt = 'POINT(-68.12 -16.50)';

    $this->branch = Branch::create([
        'id' => (string) Str::uuid(),
        'name' => 'Sucursal Central La Paz',
        'slug' => 'central-lpz',
        'city' => 'La Paz',
        'is_active' => true,
        'is_default' => true,
        'is_operational_paused' => false,
        'location' => DB::raw("ST_SRID(ST_GeomFromText('{$pointWkt}'), 4326)"),
        'coverage_polygon' => DB::raw("ST_SRID(ST_GeomFromText('{$polygonWkt}'), 4326)"),
    ]);
});

test('registro normaliza numero telefonico boliviano a formato e164', function (string $inputPhone, string $expectedPhone): void {
    $response = $this->post(route('customer.register.store'), [
        'phone' => $inputPhone,
        'email' => 'cliente_' . Str::random(5) . '@test.com',
        'password' => 'SecPassword123!',
        'password_confirmation' => 'SecPassword123!',
        'country_code' => 'BO',
        'latitude' => -16.5000,
        'longitude' => -68.1200,
        'first_name' => 'Juan',
        'last_name' => 'Perez',
        'avatar_source' => 'avatar_1.png',
        'address' => 'Av. 6 de Agosto #1234',
    ]);

    $response->assertRedirect(route('customer.index'));

    $this->assertDatabaseHas('customers', [
        'phone' => $expectedPhone,
        'deleted_epoch' => 0,
    ]);
})->with([
    ['71234567', '+71234567'],
    ['+59171234567', '+59171234567'],
    ['591 71234567', '+59171234567'],
    ['+591-712-34567', '+59171234567'],
]);

test('falla el registro si password confirmation no coincide', function (): void {
    $response = $this->post(route('customer.register.store'), [
        'phone' => '+59171234567',
        'email' => 'test@test.com',
        'password' => 'Password123!',
        'password_confirmation' => 'MismatchPassword',
        'latitude' => -16.5000,
        'longitude' => -68.1200,
        'first_name' => 'Juan',
        'last_name' => 'Perez',
        'avatar_source' => 'avatar_1.png',
        'address' => 'Av. 6 de Agosto',
    ]);

    $response->assertSessionHasErrors('password');
    $this->assertDatabaseCount('customers', 0);
});