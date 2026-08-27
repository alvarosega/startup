<?php

declare(strict_types=1);

use App\Models\Users\Driver;
use App\Models\Users\DriverProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Storage::fake('local');

    Role::firstOrCreate([
        'name' => 'driver',
        'guard_name' => 'driver',
    ]);
});

test('registro de chofer almacena documentos de kyc en storage privado', function (): void {
    $ciFile = UploadedFile::fake()->image('ci_front.jpg', 800, 600)->size(2048);
    $licenseFile = UploadedFile::fake()->image('license.png', 800, 600)->size(1024);

    $response = $this->post(route('driver.register.store'), [
        'phone' => '+59176543210',
        'email' => 'driver_kyc@test.com',
        'password' => 'StrongPass123!',
        'password_confirmation' => 'StrongPass123!',
        'first_name' => 'Pedro',
        'last_name' => 'Mamani',
        'license_number' => 'LP-98765432',
        'license_plate' => '4567XYZ',
        'vehicle_type' => 'moto',
        'avatar_source' => 'avatar_1.svg',
        'ci_front' => $ciFile,
        'license_photo' => $licenseFile,
    ]);

    $response->assertRedirect(route('driver.profile.index'));

    $driver = Driver::where('phone', '+59176543210')->first();
    expect($driver)->not->toBeNull();

    $profile = DriverProfile::where('driver_id', $driver->id)->first();
    expect($profile->ci_front_path)->not->toBeNull();
    expect($profile->license_photo_path)->not->toBeNull();

    Storage::disk('local')->assertExists($profile->ci_front_path);
    Storage::disk('local')->assertExists($profile->license_photo_path);
});

test('falla la subida si el archivo supera el limite de 4096 kb', function (): void {
    $oversizedFile = UploadedFile::fake()->create('heavy_doc.jpg', 4097, 'image/jpeg');

    $response = $this->post(route('driver.register.store'), [
        'phone' => '+59176543211',
        'email' => 'driver_heavy@test.com',
        'password' => 'StrongPass123!',
        'password_confirmation' => 'StrongPass123!',
        'first_name' => 'Pedro',
        'last_name' => 'Mamani',
        'license_number' => 'LP-1111111',
        'license_plate' => '1111XYZ',
        'vehicle_type' => 'moto',
        'avatar_source' => 'avatar_1.svg',
        'ci_front' => $oversizedFile,
        'license_photo' => UploadedFile::fake()->image('license.png'),
    ]);

    $response->assertSessionHasErrors('ci_front');
    $this->assertDatabaseCount('drivers', 0);
});