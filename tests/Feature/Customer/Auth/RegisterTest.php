<?php

/**
 * Ruta del Archivo: tests/Feature/Customer/Auth/RegisterTest.php
 * Comando de Ejecución: php artisan test tests/Feature/Customer/Auth/RegisterTest.php
 */

declare(strict_types=1);

use App\Models\Users\Customer;
use App\Services\Customer\Queries\BranchQueryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->branchAId = '9b2a1c3d-4e5f-6a7b-8c9d-0e1f2a3b4c5d';
    $this->branchBId = '8a1b2c3d-4e5f-6a7b-8c9d-0e1f2a3b4c5e';

    DB::table('branches')->insert([
        [
            'id' => $this->branchAId,
            'name' => 'Sucursal Zona Norte',
            'slug' => 'sucursal-zona-norte',
            'city' => 'La Paz',
            'phone' => '+5912222222',
            'address' => 'Av. Ballivian Nro 100',
            'location' => DB::raw("ST_GeomFromText('POINT(-16.5000 -68.1500)')"),
            'coverage_polygon' => DB::raw("ST_GeomFromText('POLYGON((-16.49 -68.16, -16.49 -68.14, -16.51 -68.14, -16.51 -68.16, -16.49 -68.16))')"),
            'delivery_base_fee' => 10.00,
            'delivery_price_per_km' => 2.50,
            'surge_multiplier' => 1.00,
            'min_order_amount' => 50.00,
            'small_order_fee' => 5.00,
            'base_service_fee_percentage' => 5.00,
            'is_default' => false,
            'is_active' => true,
            'deleted_epoch' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => $this->branchBId,
            'name' => 'Sucursal Zona Sur',
            'slug' => 'sucursal-zona-sur',
            'city' => 'La Paz',
            'phone' => '+5912222223',
            'address' => 'Calle 21 de Calacoto',
            'location' => DB::raw("ST_GeomFromText('POINT(-16.5400 -68.0800)')"),
            'coverage_polygon' => DB::raw("ST_GeomFromText('POLYGON((-16.53 -68.09, -16.53 -68.07, -16.55 -68.07, -16.55 -68.09, -16.53 -68.09))')"),
            'delivery_base_fee' => 12.00,
            'delivery_price_per_km' => 3.00,
            'surge_multiplier' => 1.10,
            'min_order_amount' => 60.00,
            'small_order_fee' => 6.00,
            'base_service_fee_percentage' => 5.00,
            'is_default' => false,
            'is_active' => true,
            'deleted_epoch' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]
    ]);

    $this->defaultPayload = [
        'phone' => ' 712-345 67',
        'email' => 'clara.perez@example.com',
        'password' => 'Seguridad123*',
        'country_code' => 'BO',
        'latitude' => -16.5000,
        'longitude' => -68.1500,
        'branch_id' => $this->branchAId,
        'first_name' => 'Clara',
        'last_name' => 'Perez',
        'avatar_type' => 'icon',
        'avatar_source' => 'avatar_5.svg',
        'alias' => 'Casa Central',
        'address' => 'Av. Arce Edificio Los Girasoles Nro 234',
        'details' => 'Departamento 5B, tocar timbre azul',
    ];
});

/**
 * PERÍMETRO DE SEGURIDAD Y ACCESO
 */
test('authenticated customers are blocked from accessing the registration execution endpoint', function () {
    $existingCustomerId = '9b2a1c3d-4e5f-6a7b-8c9d-0e1f2a3b4f99';
    
    DB::table('customers')->insert([
        'id' => $existingCustomerId,
        'phone' => '+59171111111',
        'email' => 'active.customer@example.com',
        'password' => Hash::make('Password123*'),
        'country_code' => 'BO',
        'trust_score' => 50,
        'is_active' => true,
        'deleted_epoch' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $customerModel = new Customer();
    $customerModel->id = $existingCustomerId;
    $customerModel->exists = true;

    $response = $this->actingAs($customerModel, 'customer')
        ->post(route('register.store'), $this->defaultPayload);

    $response->assertStatus(302);
    $response->assertRedirect('/');
    
    expect(auth('customer')->check())->toBeTrue();
});

/**
 * ESCENARIOS HAPPY PATH (PERSISTENCIA Y TRANSACCIONALIDAD ATÓMICA)
 */
test('registers a new customer successfully when located within a single unique branch logistics coverage area', function () {
    $mockQueryService = mock(BranchQueryService::class);
    $mockQueryService->shouldReceive('getCollidingBranches')
        ->once()
        ->with(-16.5000, -68.1500)
        ->andReturn([['id' => $this->branchAId]]);
        
    $this->app->instance(BranchQueryService::class, $mockQueryService);

    $response = $this->post(route('register.store'), $this->defaultPayload);

    $response->assertStatus(302);
    $response->assertRedirect(route('index'));

    expect(auth('customer')->check())->toBeTrue();
    $authenticatedCustomer = auth('customer')->user();
    expect($authenticatedCustomer->email)->toBe('clara.perez@example.com');

    $this->assertDatabaseHas('customers', [
        'email' => 'clara.perez@example.com',
        'phone' => '+59171234567',
        'branch_id' => $this->branchAId,
        'country_code' => 'BO',
        'is_active' => 1,
        'deleted_epoch' => 0,
        'needs_password_change' => 0,
        'was_previously_deleted' => 0,
    ]);

    $this->assertDatabaseHas('customer_profiles', [
        'customer_id' => $authenticatedCustomer->id,
        'first_name' => 'Clara',
        'last_name' => 'Perez',
        'avatar_type' => 'icon',
        'avatar_source' => 'avatar_5.svg',
    ]);

    $this->assertDatabaseHas('customer_addresses', [
        'customer_id' => $authenticatedCustomer->id,
        'branch_id' => $this->branchAId,
        'alias' => 'Casa Central',
        'address' => 'Av. Arce Edificio Los Girasoles Nro 234',
        'reference' => 'Departamento 5B, tocar timbre azul',
    ]);
});

test('registers a new customer successfully when technical coordinates drop into a logistical branch intersection zone', function () {
    $mockQueryService = mock(BranchQueryService::class);
    $mockQueryService->shouldReceive('getCollidingBranches')
        ->once()
        ->with(-16.5000, -68.1500)
        ->andReturn([['id' => $this->branchAId], ['id' => $this->branchBId]]);
        
    $this->app->instance(BranchQueryService::class, $mockQueryService);

    $payload = $this->defaultPayload;
    $payload['branch_id'] = $this->branchBId;

    $response = $this->post(route('register.store'), $payload);

    $response->assertStatus(302);
    $response->assertRedirect(route('index'));

    $this->assertDatabaseHas('customers', [
        'email' => 'clara.perez@example.com',
        'branch_id' => $this->branchBId,
        'deleted_epoch' => 0,
    ]);
});

test('registers a new customer successfully when location resides completely outside delivery coverage and branch_id is null', function () {
    $mockQueryService = mock(BranchQueryService::class);
    $mockQueryService->shouldReceive('getCollidingBranches')
        ->once()
        ->with(-16.9000, -68.9000)
        ->andReturn([]);
        
    $this->app->instance(BranchQueryService::class, $mockQueryService);

    $payload = $this->defaultPayload;
    $payload['latitude'] = -16.9000;
    $payload['longitude'] = -68.9000;
    $payload['branch_id'] = null;

    $response = $this->post(route('register.store'), $payload);

    $response->assertStatus(302);
    $response->assertRedirect(route('index'));

    $this->assertDatabaseHas('customers', [
        'email' => 'clara.perez@example.com',
        'branch_id' => null,
        'deleted_epoch' => 0,
    ]);

    $this->assertDatabaseHas('customer_addresses', [
        'branch_id' => null,
        'address' => 'Av. Arce Edificio Los Girasoles Nro 234',
    ]);
});

/**
 * ESCENARIOS DE ERROR DE VALIDACIÓN LOGÍSTICA (COBERTURA GEOGRÁFICA)
 */
test('rejects registration when location is outside delivery coverage but an operational branch_id is forced in the request', function () {
    $mockQueryService = mock(BranchQueryService::class);
    $mockQueryService->shouldReceive('getCollidingBranches')
        ->once()
        ->with(-16.9000, -68.9000)
        ->andReturn([]);
        
    $this->app->instance(BranchQueryService::class, $mockQueryService);

    $payload = $this->defaultPayload;
    $payload['latitude'] = -16.9000;
    $payload['longitude'] = -68.9000;
    $payload['branch_id'] = $this->branchAId;

    $response = $this->post(route('register.store'), $payload);

    $response->assertStatus(302);
    $response->assertSessionHasErrors(['branch_id']);
    
    $errors = session('errors')->get('branch_id');
    expect($errors[0])->toBe('La ubicación se encuentra fuera de la zona de cobertura. No se permite la asignación de sucursal.');

    $this->assertDatabaseCount('customers', 0);
});

test('rejects registration when customer selects a branch that does not mathematically match their unique demarcated coverage zone', function () {
    $mockQueryService = mock(BranchQueryService::class);
    $mockQueryService->shouldReceive('getCollidingBranches')
        ->once()
        ->with(-16.5000, -68.1500)
        ->andReturn([['id' => $this->branchAId]]);
        
    $this->app->instance(BranchQueryService::class, $mockQueryService);

    $payload = $this->defaultPayload;
    $payload['branch_id'] = $this->branchBId; 

    $response = $this->post(route('register.store'), $payload);

    $response->assertStatus(302);
    $response->assertSessionHasErrors(['branch_id']);
    
    $errors = session('errors')->get('branch_id');
    expect($errors[0])->toBe('La sucursal seleccionada no coincide con la demarcación única de cobertura para esa zona.');

    $this->assertDatabaseCount('customers', 0);
});

test('rejects registration inside an intersection zone when branch_id is omitted or does not belong to the colliding branches list', function () {
    $mockQueryService = mock(BranchQueryService::class);
    $mockQueryService->shouldReceive('getCollidingBranches')
        ->once()
        ->with(-16.5000, -68.1500)
        ->andReturn([['id' => $this->branchAId], ['id' => $this->branchBId]]);
        
    $this->app->instance(BranchQueryService::class, $mockQueryService);

    $payload = $this->defaultPayload;
    $payload['branch_id'] = '9b2a1c3d-4e5f-6a7b-8c9d-999999999999';

    $response = $this->post(route('register.store'), $payload);

    $response->assertStatus(302);
    $response->assertSessionHasErrors(['branch_id']);
    
    $errors = session('errors')->get('branch_id');
    expect($errors[0])->toBe('Su ubicación se encuentra en una intersección logística. Debe designar una sucursal válida de la lista.');
});

/**
 * CONTROL DE IDENTIDADES DUPLICADAS (REGLAS DE IDENTIDAD GLOBAL CON DELETED_EPOCH)
 */
test('blocks registration if the normalized phone number or email is already tied to an active customer account', function () {
    DB::table('customers')->insert([
        'id' => '9b2a1c3d-4e5f-6a7b-8c9d-0e1f2a3b4f55',
        'phone' => '+59171234567', 
        'email' => 'clara.perez@example.com',
        'password' => Hash::make('Secret123*'),
        'country_code' => 'BO',
        'deleted_epoch' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $mockQueryService = mock(BranchQueryService::class);
    $mockQueryService->shouldReceive('getCollidingBranches')->andReturn([['id' => $this->branchAId]]);
    $this->app->instance(BranchQueryService::class, $mockQueryService);

    $response = $this->post(route('register.store'), $this->defaultPayload);

    $response->assertStatus(302);
    $response->assertSessionHasErrors(['phone', 'email']);

    expect(session('errors')->get('phone')[0])->toBe('El teléfono ingresado ya está registrado y activo en el sistema.');
    expect(session('errors')->get('email')[0])->toBe('El correo electrónico ingresado ya está registrado y activo en el sistema.');
});

test('allows registration if the telephone number or email matches a historically soft-deleted account record', function () {
    DB::table('customers')->insert([
        'id' => '9b2a1c3d-4e5f-6a7b-8c9d-0e1f2a3b4f44',
        'phone' => '+59171234567',
        'email' => 'clara.perez@example.com',
        'password' => Hash::make('SecretOld123*'),
        'country_code' => 'BO',
        'deleted_epoch' => 1718239200, 
        'created_at' => now()->subYear(),
        'updated_at' => now()->subYear(),
    ]);

    $mockQueryService = mock(BranchQueryService::class);
    $mockQueryService->shouldReceive('getCollidingBranches')->andReturn([['id' => $this->branchAId]]);
    $this->app->instance(BranchQueryService::class, $mockQueryService);

    $response = $this->post(route('register.store'), $this->defaultPayload);

    $response->assertStatus(302);
    $response->assertRedirect(route('index'));

    $this->assertDatabaseCount('customers', 2);
    
    $this->assertDatabaseHas('customers', [
        'email' => 'clara.perez@example.com',
        'phone' => '+59171234567',
        'deleted_epoch' => 0,
    ]);
});

/**
 * PRUEBA DE ATOMICIDAD DE BASE DE DATOS (MANEJO DE TRANSACCIONES / ROLLBACK COMPLETO)
 */
test('triggers full database rollback across all tables if an internal system exception occurs during the profile generation phase', function () {
    $mockQueryService = mock(BranchQueryService::class);
    $mockQueryService->shouldReceive('getCollidingBranches')->andReturn([['id' => $this->branchAId]]);
    $this->app->instance(BranchQueryService::class, $mockQueryService);

    Event::listen('eloquent.saving: *', function ($event) {
        if (str_contains($event, 'CustomerProfile') || str_contains($event, 'Profile')) {
            throw new \RuntimeException('Simulated runtime database engine write collision anomaly.');
        }
    });

    $response = $this->post(route('register.store'), $this->defaultPayload);

    $response->assertStatus(500);

    $this->assertDatabaseCount('customers', 0);
    $this->assertDatabaseCount('customer_profiles', 0);
    $this->assertDatabaseCount('customer_addresses', 0);
});

/**
 * MATRIZ DE VALIDACIÓN DE CAMPOS Y LÍMITES TÉCNICOS (DATASETS)
 */
test('rejects execution when processing out of bounds data formats', function (array $invalidData, string $expectedErrorKey) {
    $mockQueryService = mock(BranchQueryService::class);
    $mockQueryService->shouldReceive('getCollidingBranches')->andReturn([['id' => $this->branchAId]]);
    $this->app->instance(BranchQueryService::class, $mockQueryService);

    $payload = array_merge($this->defaultPayload, $invalidData);

    $response = $this->post(route('register.store'), $payload);

    $response->assertStatus(302);
    $response->assertSessionHasErrors([$expectedErrorKey]);
    $this->assertDatabaseCount('customers', 0);
})->with('invalid_registration_data');

dataset('invalid_registration_data', [
    'password below minimum length' => [['password' => 'short'], 'password'],
    'country code exceeds boundary length' => [['country_code' => 'BOLIVIA'], 'country_code'],
    'latitude beyond technical boundary high' => [['latitude' => 90.0001], 'latitude'],
    'latitude beyond technical boundary low' => [['latitude' => -90.0001], 'latitude'],
    'longitude beyond technical boundary high' => [['longitude' => 180.0001], 'longitude'],
    'longitude beyond technical boundary low' => [['longitude' => -180.0001], 'longitude'],
    'missing required structural first name' => [['first_name' => ''], 'first_name'],
    'missing required structural email format' => [['email' => 'invalid-email-string'], 'email'],
    'malformed branch identifier type' => [['branch_id' => 'non-uuid-corrupted-string'], 'branch_id'],
    'avatar type payload size violation' => [['avatar_type' => str_repeat('a', 51)], 'avatar_type'],
    'avatar source payload size violation' => [['avatar_source' => str_repeat('b', 256)], 'avatar_source'],
]);