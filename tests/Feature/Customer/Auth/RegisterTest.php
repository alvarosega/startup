<?php

declare(strict_types=1);

use App\Models\Operations\Branch;
use App\Models\Users\Customer;
use App\Actions\Customer\Cart\SyncGuestCartAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->branch = Branch::factory()->create([
        'id' => Str::uuid7()->toString(),
        'is_active' => true,
        'is_default' => true
    ]);

    // ← AÑADIR ESTA LÍNEA: Inicializa el rol requerido por el Action de registro
    Role::create(['name' => 'customer', 'guard_name' => 'customer']);

    $this->syncCartMock = mock(SyncGuestCartAction::class);
    $this->app->instance(SyncGuestCartAction::class, $this->syncCartMock);
});

it('registra un nuevo cliente b2c procesando datos espaciales en mysql', function () {
    session(['guest_client_uuid' => (string) Str::uuid()]);

    $this->syncCartMock
        ->shouldReceive('execute')
        ->once()
        ->andReturn(true);

    $payload = [
        'phone' => '+59171111111',
        'email' => 'newcustomer@b2c.com',
        'password' => 'Password123*',
        'password_confirmation' => 'Password123*',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'address' => 'Av. Arce #1234',
        'country_code' => 'BO',
        'latitude' => -16.500000,
        'longitude' => -68.150000,
        'avatar_type' => 'icon',
        'avatar_source' => 'avatar_1.png',
        'branch_id' => $this->branch->id,
    ];

    $response = $this->post(route('customer.register.store'), $payload, [
        'X-Idempotency-Key' => (string) Str::uuid(),
    ]);

    $response->assertRedirect(route('customer.index'));
    $response->assertSessionHas('status', 'Registro completado con éxito.');

    $this->assertDatabaseHas('customers', [
        'email' => 'newcustomer@b2c.com',
        'phone' => '+59171111111',
        'branch_id' => $this->branch->id,
    ]);

    $customer = Customer::where('email', 'newcustomer@b2c.com')->first();
    $spatialCheck = DB::select("SELECT ST_AsText(last_known_location) as location FROM customers WHERE id = ?", [$customer->id]);
    
    expect($spatialCheck[0]->location)->toBe('POINT(-16.5 -68.15)');
    expect(Auth::guard('customer')->check())->toBeTrue();
});

it('valida el paso uno del registro de forma sincrona', function () {
    $response = $this->postJson(route('customer.register.validate_step1'), [
        'phone' => '+59172222222',
    ]);

    $response->assertStatus(200);
    $response->assertJson(['valid' => true]);
});

// CORRECCIÓN: Test de colisión de teléfono aislado
it('deniega registro por colision de telefono con usuario activo', function () {
    Customer::factory()->create([
        'phone' => '+59173333333',
        'email' => 'active-email-1@b2c.com',
        'deleted_epoch' => 0,
    ]);

    $payload = [
        'phone' => '+59173333333',
        'email' => 'different-email@b2c.com',
        'password' => 'Password123*',
        'password_confirmation' => 'Password123*',
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'address' => 'Calle 5',
        'country_code' => 'BO',
        'latitude' => -16.500000,
        'longitude' => -68.150000,
        'avatar_type' => 'icon',
        'avatar_source' => 'avatar_2.png',
    ];

    $response = $this->post(route('customer.register.store'), $payload);
    $response->assertSessionHasErrors(['phone']);
});

// CORRECCIÓN: Test de colisión de email aislado
it('deniega registro por colision de email con usuario activo', function () {
    Customer::factory()->create([
        'phone' => '+59179999999',
        'email' => 'collision@b2c.com',
        'deleted_epoch' => 0,
    ]);

    $payload = [
        'phone' => '+59173333333',
        'email' => 'collision@b2c.com',
        'password' => 'Password123*',
        'password_confirmation' => 'Password123*',
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'address' => 'Calle 5',
        'country_code' => 'BO',
        'latitude' => -16.500000,
        'longitude' => -68.150000,
        'avatar_type' => 'icon',
        'avatar_source' => 'avatar_2.png',
    ];

    $response = $this->post(route('customer.register.store'), $payload);
    $response->assertSessionHasErrors(['email']);
});

// CORRECCIÓN: Alineación del target_type al formato del validador de la app
it('intercepta registro exponiendo el motivo de eliminacion logica desde los logs de auditoria', function () {
    $deletedCustomerId = Str::uuid7()->toString();
    
    DB::table('customers')->insert([
        'id' => $deletedCustomerId,
        'phone' => '+59174444444',
        'email' => 'deleted@b2c.com',
        'password' => bcrypt('123456'),
        'deleted_epoch' => time(),
        'deleted_at' => now()->toDateTimeString(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('audit_logs')->insert([
        'id' => Str::uuid()->toString(),
        'target_type' => 'App\Models\Users\Customer', // ← MODIFICAR: Debe incluir '\Users\'
        'target_id' => $deletedCustomerId,
        'action' => 'force_delete',
        'payload_before' => json_encode(['rejection_reason' => 'Violación de términos de servicio por fraude B2C']),
        'causer_type' => 'App\Models\Admin',
        'causer_id' => Str::uuid7()->toString(),
        'created_at' => now()->toDateTimeString(),
    ]);

    $payload = [
        'phone' => '+59174444444',
        'email' => 'new-email-allowed@b2c.com', // Se aísla el conflicto sobre el teléfono para asegurar el comportamiento de la regla
        'password' => 'Password123*',
        'password_confirmation' => 'Password123*',
        'first_name' => 'Fraudulent',
        'last_name' => 'User',
        'address' => 'Fake Address',
        'country_code' => 'BO',
        'latitude' => -16.500000,
        'longitude' => -68.150000,
        'avatar_type' => 'icon',
        'avatar_source' => 'avatar_3.png',
    ];

    $response = $this->post(route('customer.register.store'), $payload);
    $response->assertSessionHasErrors(['phone']);
    
    $errors = session('errors');
    expect($errors->first('phone'))->toContain('Violación de términos de servicio por fraude B2C');
});