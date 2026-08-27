<?php

declare(strict_types=1);

use App\Models\Operations\Branch;
use App\Models\Users\Customer;
use App\Models\Users\CustomerAddress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->customer = Customer::create([
        'id'           => (string) Str::uuid(),
        'phone'        => '+59170099887',
        'email'        => 'spatial_user@test.com',
        'password'     => 'Password123!',
        'country_code' => 'BO',
        'is_active'    => true,
    ]);

    // Zona Central: Longitud -68.15 a -68.10, Latitud -16.55 a -16.48 (Formato long-lat)
    $polygonCentral = 'POLYGON((-68.15 -16.55, -68.10 -16.55, -68.10 -16.48, -68.15 -16.48, -68.15 -16.55))';
    $this->branchCentral = Branch::create([
        'id'               => (string) Str::uuid(),
        'name'             => 'Zona Central',
        'slug'             => 'central',
        'is_active'        => true,
        'is_default'       => false,
        'location'         => DB::raw("ST_GeomFromText('POINT(-68.12 -16.50)', 4326, 'axis-order=long-lat')"),
        'coverage_polygon' => DB::raw("ST_GeomFromText('{$polygonCentral}', 4326, 'axis-order=long-lat')"),
    ]);

    // Zona Sur: Longitud -68.08 a -68.02, Latitud -16.58 a -16.52 (Sin solapamiento)
    $polygonDefault = 'POLYGON((-68.08 -16.58, -68.02 -16.58, -68.02 -16.52, -68.08 -16.52, -68.08 -16.58))';
    $this->branchDefault = Branch::create([
        'id'               => (string) Str::uuid(),
        'name'             => 'Sucursal Base Default',
        'slug'             => 'default',
        'is_active'        => true,
        'is_default'       => true,
        'location'         => DB::raw("ST_GeomFromText('POINT(-68.05 -16.55)', 4326, 'axis-order=long-lat')"),
        'coverage_polygon' => DB::raw("ST_GeomFromText('{$polygonDefault}', 4326, 'axis-order=long-lat')"),
    ]);
});

test('registro dentro de poligono asocia la sucursal de cobertura', function (): void {
    $response = $this->post(route('customer.register.store'), [
        'phone'                 => '+59178877665',
        'email'                 => 'geo_inside@test.com',
        'password'              => 'Password123!',
        'password_confirmation' => 'Password123!',
        'latitude'              => -16.5100,
        'longitude'             => -68.1300,
        'first_name'            => 'Geo',
        'last_name'             => 'Inside',
        'avatar_source'         => 'avatar_1.svg',
        'address'               => 'Calle Murillo #500',
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('customer.index'));

    $this->assertDatabaseHas('customer_addresses', [
        'branch_id' => $this->branchCentral->id,
        'address'   => 'CALLE MURILLO #500',
    ]);
});

test('al marcar una nueva direccion como default las anteriores pasan a false', function (): void {
    $this->actingAs($this->customer, 'customer');

    $address1 = CustomerAddress::create([
        'id'          => (string) Str::uuid(),
        'customer_id' => $this->customer->id,
        'branch_id'   => $this->branchCentral->id,
        'alias'       => 'Casa',
        'address'     => 'Direccion Original',
        'position'    => DB::raw("ST_GeomFromText('POINT(-68.13 -16.51)', 4326, 'axis-order=long-lat')"),
        'is_default'  => true,
    ]);

    $response = $this->post(route('customer.profile.addresses.store'), [
        'branch_id'  => $this->branchCentral->id,
        'alias'      => 'Trabajo',
        'address'    => 'Direccion Nueva',
        'latitude'   => -16.5100,
        'longitude'  => -68.1300,
        'is_default' => true,
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect();

    expect($address1->fresh()->is_default)->toBeFalse();
});