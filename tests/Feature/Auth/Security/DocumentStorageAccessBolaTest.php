<?php

declare(strict_types=1);

use App\Models\Users\Driver;
use App\Models\Users\DriverProfile;
use App\Models\Users\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Storage::fake('local');

    $this->driverOwner = Driver::create([
        'id' => (string) Str::uuid(),
        'phone' => '+59171000001',
        'email' => 'owner@driver.com',
        'password' => 'Password123!',
        'status' => 'approved',
    ]);

    $this->driverAttacker = Driver::create([
        'id' => (string) Str::uuid(),
        'phone' => '+59171000002',
        'email' => 'attacker@driver.com',
        'password' => 'Password123!',
        'status' => 'approved',
    ]);

    Storage::disk('local')->put("drivers/{$this->driverOwner->id}/documents/license.jpg", 'DOCUMENTO_CONFIDENCIAL');
});

test('chofer no autorizado o cliente no puede acceder a documentos de otro chofer por idor', function (): void {
    $this->actingAs($this->driverAttacker, 'driver');

    // Suponiendo un endpoint de streaming controlado de documentos
    $response = $this->get("/api/drivers/{$this->driverOwner->id}/documents/license.jpg");

    expect(in_array($response->status(), [401, 403, 404]))->toBeTrue();
});