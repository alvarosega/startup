<?php

declare(strict_types=1);

use App\Models\Users\Driver;
use App\Models\Users\DriverProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->password = 'DriverPassword123!';
});

test('chofer con estado approved es redirigido a dashboard tras login', function (): void {
    $driver = Driver::create([
        'id' => (string) Str::uuid(),
        'phone' => '+59171122334',
        'email' => 'driver_approved@test.com',
        'password' => Hash::make($this->password),
        'status' => 'approved',
    ]);

    $response = $this->post(route('driver.login.store'), [
        'phone' => '+59171122334',
        'password' => $this->password,
    ]);

    $response->assertRedirect(route('driver.dashboard'));
    $this->assertAuthenticatedAs($driver, 'driver');
});

test('chofer con estado pending es canalizado a profile.index tras login', function (): void {
    $driver = Driver::create([
        'id' => (string) Str::uuid(),
        'phone' => '+59172233445',
        'email' => 'driver_pending@test.com',
        'password' => Hash::make($this->password),
        'status' => 'pending',
    ]);

    $response = $this->post(route('driver.login.store'), [
        'phone' => '+59172233445',
        'password' => $this->password,
    ]);

    $response->assertRedirect(route('driver.profile.index'));
    $this->assertAuthenticatedAs($driver, 'driver');
});

test('chofer con estado rejected es redirigido a profile para subsanacion', function (): void {
    $driver = Driver::create([
        'id' => (string) Str::uuid(),
        'phone' => '+59173344556',
        'email' => 'driver_rejected@test.com',
        'password' => Hash::make($this->password),
        'status' => 'rejected',
    ]);

    DriverProfile::create([
        'driver_id' => $driver->id,
        'first_name' => 'Carlos',
        'last_name' => 'Chofer',
        'rejection_reason' => 'Licencia de conducir vencida.',
    ]);

    $response = $this->post(route('driver.login.store'), [
        'phone' => '+59173344556',
        'password' => $this->password,
    ]);

    $response->assertRedirect(route('driver.profile.index'));
});