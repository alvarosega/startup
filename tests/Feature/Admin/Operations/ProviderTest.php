<?php


use App\Models\Users\Admin;
use App\Models\Operations\Provider;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'super_admin']);
});


test('un super admin puede crear un proveedor, validando la generacion automatica de slug e internal_code', function () {
    $superAdmin = Admin::factory()->create(['is_active' => true]);
    $superAdmin->assignRole('super_admin');

    $payload = [
        'company_name' => 'Distribuidora Altiplano S.A.',
        'commercial_name' => 'Altiplano Mayoristas',
        'tax_id' => 'NIT-994857102',
        'internal_code' => null, 
        'contact_name' => 'Jorge Mendoza',
        'email_orders' => 'pedidos@altiplano.com',
        'phone' => '+59122114455',
        'address' => 'Zona Industrial Hangar 4',
        'city' => 'El Alto',
        'lead_time_days' => 3,
        'min_order_value' => 1500.00,
        'credit_days' => 30,
        'credit_limit' => 50000.00,
        'is_active' => true,
        'notes' => 'Proveedor principal de abarrotes.',
    ];

    $response = $this->actingAs($superAdmin, 'super_admin')
        ->from(route('admin.operations.providers.create'))
        ->post(route('admin.operations.providers.store'), $payload);

    $response->assertStatus(302);
    $response->assertRedirect(route('admin.operations.providers.index'));

    $this->assertDatabaseHas('providers', [
        'company_name' => 'Distribuidora Altiplano S.A.',
        'tax_id' => 'NIT-994857102',
        'slug' => 'distribuidora-altiplano-sa', 
        'is_active' => true,
        'deleted_epoch' => 0,
    ]);

    $provider = DB::table('providers')->where('tax_id', 'NIT-994857102')->first();
    expect($provider->internal_code)->not->toBeNull()
        ->and($provider->internal_code)->not->toBeEmpty();
});

test('un super admin puede actualizar un proveedor existente cumpliendo el contrato tecnico de modificacion', function () {
    $superAdmin = Admin::factory()->create(['is_active' => true]);
    $superAdmin->assignRole('super_admin');

    $providerId = Str::uuid()->toString();
    DB::table('providers')->insert([
        'id' => $providerId,
        'company_name' => 'Proveedor Original',
        'slug' => 'proveedor-original',
        'tax_id' => 'NIT-11111111',
        'internal_code' => 'PROV-001',
        'lead_time_days' => 2,
        'min_order_value' => 500.00,
        'credit_days' => 10,
        'credit_limit' => 10000.00,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $updatePayload = [
        'company_name' => 'Proveedor Actualizado',
        'commercial_name' => 'Nombre Comercial S.A.',
        'tax_id' => 'NIT-22222222', 
        'internal_code' => 'PROV-001-MOD',
        'version' => 1, 
        'contact_name' => 'Luciana Cortez',
        'email_orders' => 'luciana@orders.com',
        'phone' => '+59144332211',
        'address' => 'Av. Melchor Pinto #45',
        'city' => 'Santa Cruz',
        'lead_time_days' => 5, 
        'min_order_value' => 2000.00,
        'credit_days' => 15,
        'credit_limit' => 25000.00,
        'is_active' => false, 
        'notes' => 'Cambio de políticas de crédito.',
    ];

    $response = $this->actingAs($superAdmin, 'super_admin')
        ->from(route('admin.operations.providers.edit', ['provider' => $providerId]))
        ->put(route('admin.operations.providers.update', ['provider' => $providerId]), $updatePayload);

    $response->assertStatus(302);
    $response->assertRedirect(route('admin.operations.providers.index'));

    $this->assertDatabaseHas('providers', [
        'id' => $providerId,
        'company_name' => 'Proveedor Actualizado',
        'tax_id' => 'NIT-22222222',
        'internal_code' => 'PROV-001-MOD',
        'is_active' => false,
    ]);
});



test('un administrador sin el rol super_admin no puede mutar ni crear proveedores en el sistema', function () {
    $unauthorizedAdmin = Admin::factory()->create(['is_active' => true]);

    $payload = [
        'company_name' => 'Intrusion Corp',
        'tax_id' => 'NIT-66666666',
        'lead_time_days' => 1,
        'min_order_value' => 100.00,
        'credit_days' => 0,
        'credit_limit' => 0,
        'is_active' => true,
    ];

    $response = $this->actingAs($unauthorizedAdmin, 'super_admin')
        ->post(route('admin.operations.providers.store'), $payload);

    $response->assertStatus(403);
    $this->assertDatabaseMissing('providers', ['company_name' => 'Intrusion Corp']);
});

dataset('payloads_proveedores_invalidos_store', [
    'razon social ausente' => [['company_name' => ''], 'company_name'],
    'tax_id ausente' => [['tax_id' => ''], 'tax_id'],
    'formato email erroneo' => [['email_orders' => 'not-an-email'], 'email_orders'],
    'valores financieros negativos' => [['min_order_value' => -50.00], 'min_order_value'],
    'dias de credito negativos' => [['credit_days' => -5], 'credit_days'],
]);

test('el contrato tecnico de creacion valida estrictamente tipos de datos nulos y restricciones minimas', function (array $invalidData, string $expectedErrorKey) {
    $superAdmin = Admin::factory()->create(['is_active' => true]);
    $superAdmin->assignRole('super_admin');

    $payload = array_merge([
        'company_name' => 'Validacion Proveedor',
        'tax_id' => 'NIT-8888888',
        'lead_time_days' => 1,
        'min_order_value' => 10.00,
        'credit_days' => 0,
        'credit_limit' => 0,
        'is_active' => true,
    ], $invalidData);

    $response = $this->actingAs($superAdmin, 'super_admin')
        ->from(route('admin.operations.providers.create'))
        ->post(route('admin.operations.providers.store'), $payload);

    $response->assertStatus(302);
    $response->assertRedirect(route('admin.operations.providers.create'));
    $response->assertSessionHasErrors($expectedErrorKey);
})->with('payloads_proveedores_invalidos_store');

test('el contrato tecnico de actualizacion rechaza un lead_time_days igual a cero segun su regla especifica', function () {
    $superAdmin = Admin::factory()->create(['is_active' => true]);
    $superAdmin->assignRole('super_admin');

    $providerId = Str::uuid()->toString();
    DB::table('providers')->insert([
        'id' => $providerId,
        'company_name' => 'Proveedor Limite',
        'slug' => 'proveedor-limite',
        'tax_id' => 'NIT-7777777',
        'lead_time_days' => 2,
        'min_order_value' => 100.00,
        'credit_days' => 0,
        'credit_limit' => 0,
        'is_active' => true,
    ]);

    $payload = [
        'company_name' => 'Proveedor Limite Modificado',
        'tax_id' => 'NIT-7777777',
        'version' => 1,
        'lead_time_days' => 0, 
        'min_order_value' => 100.00,
        'credit_days' => 0,
        'credit_limit' => 0,
        'is_active' => true,
    ];

    $response = $this->actingAs($superAdmin, 'super_admin')
        ->from(route('admin.operations.providers.edit', ['provider' => $providerId]))
        ->put(route('admin.operations.providers.update', ['provider' => $providerId]), $payload);

    $response->assertStatus(302);
    $response->assertSessionHasErrors(['lead_time_days']);
});

test('no se permite la colision de tax_id ni internal_code con otros proveedores activos', function () {
    $superAdmin = Admin::factory()->create(['is_active' => true]);
    $superAdmin->assignRole('super_admin');

    DB::table('providers')->insert([
        'id' => Str::uuid()->toString(),
        'company_name' => 'Proveedor Existente S.A.',
        'slug' => 'proveedor-existente-sa',
        'tax_id' => 'NIT-DUPLICADO-123',
        'internal_code' => 'COD-DUPLICADO-123',
        'lead_time_days' => 1,
        'min_order_value' => 10.00,
        'credit_days' => 0,
        'credit_limit' => 0,
        'is_active' => true,
    ]);

    $payload = [
        'company_name' => 'Nueva Empresa Colisionante',
        'tax_id' => 'NIT-DUPLICADO-123',
        'internal_code' => 'COD-DUPLICADO-123', 
        'lead_time_days' => 1,
        'min_order_value' => 10.00,
        'credit_days' => 0,
        'credit_limit' => 0,
        'is_active' => true,
    ];

    $response = $this->actingAs($superAdmin, 'super_admin')
        ->from(route('admin.operations.providers.create'))
        ->post(route('admin.operations.providers.store'), $payload);

    $response->assertStatus(302);
    $response->assertSessionHasErrors(['tax_id', 'internal_code']);
});


test('el sistema procesa de forma segura strings con codigo malicioso en los campos de texto de proveedores', function () {
    $superAdmin = Admin::factory()->create(['is_active' => true]);
    $superAdmin->assignRole('super_admin');

    $payload = [
        'company_name' => "Malicious Provider' OR 1=1 --",
        'commercial_name' => "<script>alert('xss_attack')</script>",
        'tax_id' => 'NIT-SECURE-INJECTION-999',
        'lead_time_days' => 2,
        'min_order_value' => 500.00,
        'credit_days' => 5,
        'credit_limit' => 2000.00,
        'is_active' => true,
    ];

    $response = $this->actingAs($superAdmin, 'super_admin')
        ->post(route('admin.operations.providers.store'), $payload);

    $response->assertStatus(302);

    $this->assertDatabaseHas('providers', [
        'company_name' => "Malicious Provider' OR 1=1 --",
        'commercial_name' => "<script>alert('xss_attack')</script>",
        'tax_id' => 'NIT-SECURE-INJECTION-999',
    ]);
});