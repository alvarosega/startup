<?php

declare(strict_types=1);

use App\Models\Operations\Branch;
use App\Models\Users\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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

    $this->customer = Customer::create([
        'id' => (string) Str::uuid(),
        'phone' => '+59170011223',
        'email' => 'customer@test.com',
        'password' => Hash::make('ValidPassword123!'),
        'country_code' => 'BO',
        'is_active' => true,
        'deleted_epoch' => 0,
    ]);
});

test('login exitoso normaliza el telefono recibido en el payload', function (string $inputPhone): void {
    $response = $this->post(route('customer.login.store'), [
        'phone' => $inputPhone,
        'password' => 'ValidPassword123!',
    ]);

    $response->assertRedirect(route('customer.index'));
    $this->assertAuthenticatedAs($this->customer, 'customer');
})->with([
    ['+59170011223'],
    ['59170011223'],
    ['+591 700 11 223'],
]);

test('login falla con password incorrecto y mantiene al usuario como invitado', function (): void {
    $response = $this->post(route('customer.login.store'), [
        'phone' => '+59170011223',
        'password' => 'WrongPassword',
    ]);

    $response->assertSessionHasErrors('phone');
    $this->assertGuest('customer');
});

test('logout de customer destruye la sesion e invalida el contexto', function (): void {
    $this->actingAs($this->customer, 'customer');

    $response = $this->post(route('customer.logout'));

    $response->assertRedirect(route('customer.login'));
    $this->assertGuest('customer');
});