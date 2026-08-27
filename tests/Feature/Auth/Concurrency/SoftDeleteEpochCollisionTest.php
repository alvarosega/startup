<?php

declare(strict_types=1);

use App\Models\Operations\Branch;
use App\Models\Users\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $polygonWkt = 'POLYGON((-68.20 -16.60, -68.05 -16.60, -68.05 -16.45, -68.20 -16.45, -68.20 -16.60))';
    Branch::create([
        'id'               => (string) Str::uuid(),
        'name'             => 'Central Ops',
        'slug'             => 'central-ops',
        'is_active'        => true,
        'is_default'       => true,
        'location'         => DB::raw("ST_GeomFromText('POINT(-68.12 -16.50)', 4326, 'axis-order=long-lat')"),
        'coverage_polygon' => DB::raw("ST_GeomFromText('{$polygonWkt}', 4326, 'axis-order=long-lat')"),
    ]);
});

test('falla el re-registro si el usuario fue dado de baja previamente', function (): void {
    $phone = '+59179988776';
    $email = 'soft_delete_test@test.com';

    Customer::create([
        'id'                     => (string) Str::uuid(),
        'phone'                  => $phone,
        'email'                  => $email,
        'password'               => 'Secret123!',
        'deleted_epoch'          => time(),
        'was_previously_deleted' => true,
    ]);

    $response = $this->post(route('customer.register.store'), [
        'phone'                 => $phone,
        'email'                 => $email,
        'password'              => 'NewPassword123!',
        'password_confirmation' => 'NewPassword123!',
        'latitude'              => -16.5000,
        'longitude'             => -68.1200,
        'first_name'            => 'Reborn',
        'last_name'             => 'User',
        'avatar_source'         => 'avatar_1.svg',
        'address'               => 'Av. 16 de Julio #200',
    ]);

    $response->assertSessionHasErrors();
    $this->assertDatabaseCount('customers', 1);
});