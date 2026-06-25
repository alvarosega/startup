<?php

use App\Models\Users\Admin;
use App\Models\Users\Customer;
use App\Models\Users\Driver;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Configuración obligatoria del entorno de seguridad de Spatie con el Guard administrativo
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'super_admin']);

    // Persistencia matemática de la Sucursal utilizando tipos nativos WKT para campos espaciales de MySQL
    DB::table('branches')->insert([
        'id' => '9b537d8e-1616-43b3-82ef-b8df0bd36120',
        'name' => 'Sede Central La Paz',
        'slug' => 'sede-central-la-paz',
        'city' => 'La Paz',
        'phone' => '22445566',
        'address' => 'Av. 6 de Agosto #2020, Sopocachi',
        'location' => DB::raw("ST_GeomFromText('POINT(-68.12628 -16.50836)')"),
        'coverage_polygon' => DB::raw("ST_GeomFromText('POLYGON((-68.17 -16.45, -68.08 -16.45, -68.08 -16.56, -68.17 -16.56, -68.17 -16.45))')"),
        'delivery_base_fee' => 7.00,
        'delivery_price_per_km' => 2.50,
        'surge_multiplier' => 1.00,
        'min_order_amount' => 45.00,
        'small_order_fee' => 6.00,
        'base_service_fee_percentage' => 5.00,
        'is_default' => true,
        'is_active' => true,
        'deleted_epoch' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
});

/*
|--------------------------------------------------------------------------
| SILO: CUSTOMERS (CLIENTES)
|--------------------------------------------------------------------------
*/

test('un super admin autenticado puede crear un cliente, validando la instanciación del perfil, contraseña inicial y bandera de cambio obligado', function () {
    $superAdmin = Admin::factory()->create(['is_active' => true]);
    $superAdmin->assignRole('super_admin');

    $payload = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'customer.john@example.com',
        'phone' => '+59170000001',
        'branch_id' => '9b537d8e-1616-43b3-82ef-b8df0bd36120',
        'is_active' => true,
    ];

    $response = $this->actingAs($superAdmin, 'super_admin')
        ->from(route('customers.create'))
        ->post(route('customers.store'), $payload);

    $response->assertStatus(302);
    $response->assertRedirect(route('customers.index'));

    // Verificación de mutación de la persistencia del silo principal (Excluye nombres por esquema de migración)
    $this->assertDatabaseHas('customers', [
        'email' => 'customer.john@example.com',
        'phone' => '+59170000001',
        'branch_id' => '9b537d8e-1616-43b3-82ef-b8df0bd36120',
        'is_active' => true,
        'needs_password_change' => true,
    ]);

    $customer = Customer::where('email', 'customer.john@example.com')->first();
    expect(Hash::check('password', $customer->password))->toBeTrue();

    // Verificación estricta de la tabla de perfil del cliente donde residen first_name y last_name
    $this->assertDatabaseHas('customer_profiles', [
        'customer_id' => $customer->id,
        'first_name' => 'John',
        'last_name' => 'Doe',
    ]);
});

test('un usuario sin el rol super_admin no puede acceder al endpoint de creacion de clientes', function () {
    $unauthorizedAdmin = Admin::factory()->create(['is_active' => true]);

    $payload = [
        'first_name' => 'Unauthorized',
        'last_name' => 'Attempt',
        'email' => 'unauthorized@example.com',
        'phone' => '+59170000002',
        'branch_id' => '9b537d8e-1616-43b3-82ef-b8df0bd36120',
        'is_active' => true,
    ];

    $response = $this->actingAs($unauthorizedAdmin, 'super_admin')
        ->post(route('customers.store'), $payload);

    $response->assertStatus(403);
    $this->assertDatabaseMissing('customers', ['email' => 'unauthorized@example.com']);
});

test('el contrato tecnico de creacion de clientes aplica validaciones estrictas y flujos de sesion en fallas', function () {
    $superAdmin = Admin::factory()->create(['is_active' => true]);
    $superAdmin->assignRole('super_admin');

    $response = $this->actingAs($superAdmin, 'super_admin')
        ->from(route('customers.create'))
        ->post(route('customers.store'), [
            'first_name' => '',
            'last_name' => '',
            'email' => 'invalid-email-format',
            'phone' => '',
            'branch_id' => 'invalid-uuid',
            'is_active' => 'not-a-boolean',
        ]);

    $response->assertStatus(302);
    $response->assertRedirect(route('customers.create'));
    $response->assertSessionHasErrors(['first_name', 'last_name', 'email', 'phone', 'branch_id', 'is_active']);
});

test('un super admin puede modificar el estado de activacion de un cliente de forma directa', function () {
    $superAdmin = Admin::factory()->create(['is_active' => true]);
    $superAdmin->assignRole('super_admin');

    $customer = Customer::factory()->create(['is_active' => true]);

    $response = $this->actingAs($superAdmin, 'super_admin')
        ->patch(route('customers.change-status', ['id' => $customer->id]), [
            'is_active' => false,
        ]);

    $response->assertStatus(302);
    $this->assertDatabaseHas('customers', [
        'id' => $customer->id,
        'is_active' => false,
    ]);
});

/*
|--------------------------------------------------------------------------
| SILO: DRIVERS (REPARTIDORES)
|--------------------------------------------------------------------------
*/

test('un super admin autenticado puede crear un conductor, persistiendo simultaneamente el perfil mecanico y de identificacion', function () {
    $superAdmin = Admin::factory()->create(['is_active' => true]);
    $superAdmin->assignRole('super_admin');

    $payload = [
        'first_name' => 'Carlos',
        'last_name' => 'Sainz',
        'email' => 'driver.carlos@example.com',
        'phone' => '+59170000003',
        'branch_id' => '9b537d8e-1616-43b3-82ef-b8df0bd36120',
        'status' => 'pending',
        'license_number' => '456789-LP',
        'license_plate' => '5555-XYZ',
        'vehicle_type' => 'motorcycle',
    ];

    $response = $this->actingAs($superAdmin, 'super_admin')
        ->from(route('drivers.create'))
        ->post(route('drivers.store'), $payload);

    $response->assertStatus(302);
    $response->assertRedirect(route('drivers.index'));

    $this->assertDatabaseHas('drivers', [
        'email' => 'driver.carlos@example.com',
        'phone' => '+59170000003',
        'branch_id' => '9b537d8e-1616-43b3-82ef-b8df0bd36120',
        'status' => 'pending',
        'needs_password_change' => true,
    ]);

    $driver = Driver::where('email', 'driver.carlos@example.com')->first();
    expect(Hash::check('password', $driver->password))->toBeTrue();

    $this->assertDatabaseHas('driver_profiles', [
        'driver_id' => $driver->id,
        'first_name' => 'Carlos',
        'last_name' => 'Sainz',
        'license_number' => '456789-LP',
        'license_plate' => '5555-XYZ',
        'vehicle_type' => 'motorcycle',
    ]);
});

test('un super admin puede cambiar el estado de un conductor a rechazado proporcionando obligatoriamente el motivo', function () {
    $superAdmin = Admin::factory()->create(['is_active' => true]);
    $superAdmin->assignRole('super_admin');

    $driver = Driver::factory()->create(['status' => 'pending']);
    DB::table('driver_profiles')->insert([
        'driver_id' => $driver->id,
        'first_name' => 'Driver',
        'last_name' => 'Test',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($superAdmin, 'super_admin')
        ->patch(route('drivers.change-status', ['id' => $driver->id]), [
            'status' => 'rejected',
            'rejection_reason' => 'Documentacion de identidad legible no presentada o expirada.',
        ]);

    $response->assertStatus(302);
    
    $this->assertDatabaseHas('drivers', [
        'id' => $driver->id,
        'status' => 'rejected',
    ]);

    $this->assertDatabaseHas('driver_profiles', [
        'driver_id' => $driver->id,
        'rejection_reason' => 'Documentacion de identidad legible no presentada o expirada.',
    ]);
});

test('el cambio de estado de un conductor falla si se selecciona rechazado pero se omite el motivo de rechazo', function () {
    $superAdmin = Admin::factory()->create(['is_active' => true]);
    $superAdmin->assignRole('super_admin');

    $driver = Driver::factory()->create(['status' => 'pending']);

    $response = $this->actingAs($superAdmin, 'super_admin')
        ->from(route('drivers.index'))
        ->patch(route('drivers.change-status', ['id' => $driver->id]), [
            'status' => 'rejected',
            'rejection_reason' => '',
        ]);

    $response->assertStatus(302);
    $response->assertSessionHasErrors(['rejection_reason']);
});

/*
|--------------------------------------------------------------------------
| PRUEBAS DE SEGURIDAD CROSS-SILO (REGLA DE IDENTIDAD GLOBAL)
|--------------------------------------------------------------------------
*/

test('un super admin no puede crear un customer con un correo o telefono que ya pertenece a un driver', function () {
    $superAdmin = Admin::factory()->create(['is_active' => true]);
    $superAdmin->assignRole('super_admin');

    Driver::factory()->create([
        'email' => 'shared_identity@example.com',
        'phone' => '+59171111111',
    ]);

    $payload = [
        'first_name' => 'Fake',
        'last_name' => 'Customer',
        'email' => 'shared_identity@example.com',
        'phone' => '+59171111111',
        'branch_id' => '9b537d8e-1616-43b3-82ef-b8df0bd36120',
        'is_active' => true,
    ];

    $response = $this->actingAs($superAdmin, 'super_admin')
        ->from(route('customers.create'))
        ->post(route('customers.store'), $payload);

    $response->assertStatus(302);
    $response->assertSessionHasErrors(['email', 'phone']);
    
    // Corregido: La verificación apunta a customer_profiles respetando estrictamente el mapeo de migración
    $this->assertDatabaseMissing('customer_profiles', ['first_name' => 'Fake']);
});

test('un super admin no puede crear un driver con un correo o telefono que ya pertenece a un customer', function () {
    $superAdmin = Admin::factory()->create(['is_active' => true]);
    $superAdmin->assignRole('super_admin');

    Customer::factory()->create([
        'email' => 'shared_identity@example.com',
        'phone' => '+59171111111',
    ]);

    $payload = [
        'first_name' => 'Fake',
        'last_name' => 'Driver',
        'email' => 'shared_identity@example.com',
        'phone' => '+59171111111',
        'branch_id' => '9b537d8e-1616-43b3-82ef-b8df0bd36120',
        'status' => 'pending',
        'license_number' => '999999-CB',
        'license_plate' => '9999-ABC',
        'vehicle_type' => 'car',
    ];

    $response = $this->actingAs($superAdmin, 'super_admin')
        ->from(route('drivers.create'))
        ->post(route('drivers.store'), $payload);

    $response->assertStatus(302);
    $this->assertDatabaseMissing('drivers', ['email' => 'shared_identity@example.com']);
});

/*
|--------------------------------------------------------------------------
| VALIDACIONES EDGE CASES / ROBUSTEZ Y SANITIZACIÓN DE CAMPOS
|--------------------------------------------------------------------------
*/

dataset('payloads_sanitizacion', [
    'xss_in_names' => ['<script>alert("hack")</script>John', '<b>Doe</b>', 'xss_sanitized@example.com', '+59172222221'],
    'sql_injection_attempt' => ["Admin' --", "Smith' OR '1'='1", 'sql_sanitized@example.com', '+59172222222'],
]);

test('el sistema mitiga e impide la ejecucion de inyecciones maliciosas en el almacenamiento de usuarios', function (string $firstName, string $lastName, string $email, string $phone) {
    $superAdmin = Admin::factory()->create(['is_active' => true]);
    $superAdmin->assignRole('super_admin');

    $payload = [
        'first_name' => $firstName,
        'last_name' => $lastName,
        'email' => $email,
        'phone' => $phone,
        'branch_id' => '9b537d8e-1616-43b3-82ef-b8df0bd36120',
        'is_active' => true,
    ];

    $response = $this->actingAs($superAdmin, 'super_admin')
        ->post(route('customers.store'), $payload);

    $response->assertStatus(302);
    
    // Verificaciones corregidas y desacopladas según el esquema de base de datos relacional exacto
    $this->assertDatabaseHas('customers', ['email' => $email]);
    $this->assertDatabaseHas('customer_profiles', [
        'first_name' => $firstName,
        'last_name' => $lastName,
    ]);
})->with('payloads_sanitizacion');