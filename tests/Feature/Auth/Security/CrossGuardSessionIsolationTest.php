<?php

declare(strict_types=1);

use App\Models\Users\Customer;
use App\Models\Users\Driver;
use App\Models\Users\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->customer = Customer::create([
        'id' => (string) Str::uuid(),
        'phone' => '+59170000001',
        'email' => 'cust_guard@test.com',
        'password' => Hash::make('Password123!'),
        'is_active' => true,
    ]);

    $this->driver = Driver::create([
        'id' => (string) Str::uuid(),
        'phone' => '+59170000002',
        'email' => 'driver_guard@test.com',
        'password' => Hash::make('Password123!'),
        'status' => 'approved',
    ]);

    $this->admin = Admin::create([
        'id' => (string) Str::uuid(),
        'first_name' => 'Security',
        'last_name' => 'Officer',
        'email' => 'admin_guard@test.com',
        'password' => Hash::make('Password123!'),
        'is_active' => true,
    ]);
});

test('usuario autenticado como customer es rechazado en dashboard de super_admin', function (): void {
    $this->actingAs($this->customer, 'customer');

    $response = $this->get(route('dashboard.index'));

    $response->assertRedirect(route('admin.login'));
    $this->assertGuest('super_admin');
});

test('usuario autenticado como driver es rechazado en rutas de customer', function (): void {
    $this->actingAs($this->driver, 'driver');

    $response = $this->get(route('customer.profile.index'));

    $response->assertRedirect(route('customer.login'));
    $this->assertGuest('customer');
});

test('usuario autenticado como customer no puede acceder a dashboard de driver', function (): void {
    $this->actingAs($this->customer, 'customer');

    $response = $this->get(route('driver.dashboard'));

    $response->assertRedirect(route('driver.login'));
    $this->assertGuest('driver');
});