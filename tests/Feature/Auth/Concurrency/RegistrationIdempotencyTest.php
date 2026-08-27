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
    $this->branch = Branch::create([
        'id' => (string) Str::uuid(),
        'name' => 'Central Ops',
        'slug' => 'central-ops',
        'is_active' => true,
        'is_default' => true,
        'location' => DB::raw("ST_SRID(ST_GeomFromText('POINT(-68.12 -16.50)'), 4326)"),
        'coverage_polygon' => DB::raw("ST_SRID(ST_GeomFromText('{$polygonWkt}'), 4326)"),
    ]);
});

test('peticiones simultaneas con misma idempotency key persisten solo un registro', function (): void {
    $idempotencyKey = (string) Str::uuid();
    $payload = [
        'phone' => '+59178901234',
        'email' => 'concurrency@test.com',
        'password' => 'SecurePass123!',
        'password_confirmation' => 'SecurePass123!',
        'latitude' => -16.5000,
        'longitude' => -68.1200,
        'first_name' => 'Race',
        'last_name' => 'Condition',
        'avatar_source' => 'avatar_1.png',
        'address' => 'Av. Arce #100',
    ];

$responses = [];
    for ($i = 0; $i < 5; $i++) {
        $responses[] = $this->withHeader('X-Idempotency-Key', $idempotencyKey)
            ->post(route('customer.register.store'), $payload);
    }

    $createdCustomers = Customer::where('phone', '+59178901234')->get();
    expect($createdCustomers)->toHaveCount(1);
});