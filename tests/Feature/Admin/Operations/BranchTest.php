<?php


use App\Models\Users\Admin;
use App\Models\Operations\Branch;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Configuración obligatoria del entorno de seguridad de Spatie con el Guard administrativo
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'super_admin']);
});


test('un super admin puede crear una sucursal, generando el slug automatico y cerrando el poligono geometrico', function () {
    $superAdmin = Admin::factory()->create(['is_active' => true]);
    $superAdmin->assignRole('super_admin');

    $payload = [
        'name' => 'Sede Zona Sur Calacoto',
        'city' => 'La Paz',
        'phone' => '22790000',
        'address' => 'Calle 15 de Calacoto #8020',
        'latitude' => -16.54012,
        'longitude' => -68.08921,
        'coverage_polygon' => [
            [-68.10, -16.52],
            [-68.06, -16.52],
            [-68.06, -16.56],
            [-68.10, -16.56],
            [-68.10, -16.52] // Anillo geométrico cerrado explícitamente para cumplimiento estricto de MySQL
        ],
        'is_active' => true,
        'is_default' => false,
        'delivery_base_fee' => 8.50,
        'delivery_price_per_km' => 3.00,
        'surge_multiplier' => 1.20,
        'min_order_amount' => 50.00,
        'small_order_fee' => 7.00,
        'base_service_fee_percentage' => 4.50,
    ];

    $response = $this->actingAs($superAdmin, 'super_admin')
        ->from(route('admin.operations.branches.create'))
        ->post(route('admin.operations.branches.store'), $payload);

    $response->assertStatus(302);
    $response->assertRedirect(route('admin.operations.branches.index'));

    // Validación estricta de la persistencia matemática en MySQL
    $this->assertDatabaseHas('branches', [
        'name' => 'Sede Zona Sur Calacoto',
        'slug' => 'sede-zona-sur-calacoto', // Verificación de generación de slug automática basada en negocio
        'city' => 'La Paz',
        'is_default' => false,
        'deleted_epoch' => 0,
    ]);

    // Extracción y fiscalización de datos geoespaciales nativos convertidos por el backend
    $spatialCheck = DB::table('branches')
        ->where('slug', 'sede-zona-sur-calacoto')
        ->selectRaw('ST_AsText(location) as loc, ST_AsText(coverage_polygon) as poly')
        ->first();

    expect($spatialCheck->loc)->toBe('POINT(-68.08921 -16.54012)')
        ->and($spatialCheck->poly)->toBe('POLYGON((-68.1 -16.52,-68.06 -16.52,-68.06 -16.56,-68.1 -16.56,-68.1 -16.52))');
});

test('al marcar una nueva sucursal como predeterminada, las sucursales anteriores pierden esa propiedad de forma automatica', function () {
    $superAdmin = Admin::factory()->create(['is_active' => true]);
    $superAdmin->assignRole('super_admin');

    // Registrar sucursal antigua configurada como por defecto
    $oldBranchId = Str::uuid()->toString();
    DB::table('branches')->insert([
        'id' => $oldBranchId,
        'name' => 'Sede Antigua',
        'slug' => 'sede-antigua',
        'city' => 'La Paz',
        'location' => DB::raw("ST_GeomFromText('POINT(-68.12 -16.50)')"),
        'coverage_polygon' => DB::raw("ST_GeomFromText('POLYGON((-68.15 -16.48, -68.10 -16.48, -68.10 -16.52, -68.15 -16.52, -68.15 -16.48))')"),
        'is_default' => true, // Flag activo previo
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $payload = [
        'name' => 'Sede Nueva Dominante',
        'city' => 'La Paz',
        'latitude' => -16.54012,
        'longitude' => -68.08921,
        'coverage_polygon' => [[-68.10, -16.52], [-68.06, -16.52], [-68.06, -16.56], [-68.10, -16.56], [-68.10, -16.52]],
        'is_active' => true,
        'is_default' => true, // Provoca alternancia forzada por regla de exclusividad
        'delivery_base_fee' => 5.00,
        'delivery_price_per_km' => 2.00,
        'surge_multiplier' => 1.00,
        'min_order_amount' => 30.00,
        'small_order_fee' => 4.00,
        'base_service_fee_percentage' => 5.00,
    ];

    $response = $this->actingAs($superAdmin, 'super_admin')
        ->post(route('admin.operations.branches.store'), $payload);

    $response->assertStatus(302);

    // Verificación matemática de los cambios cruzados de estado en base de datos
    $this->assertDatabaseHas('branches', [
        'id' => $oldBranchId,
        'is_default' => false, // Cambiado automáticamente para salvaguardar la regla de negocio
    ]);

    $this->assertDatabaseHas('branches', [
        'name' => 'Sede Nueva Dominante',
        'is_default' => true, // Nueva sucursal se queda con la exclusividad
    ]);
});

test('un super admin puede actualizar todos los parametros operativos de una sucursal existente', function () {
    $superAdmin = Admin::factory()->create(['is_active' => true]);
    $superAdmin->assignRole('super_admin');

    $branchId = Str::uuid()->toString();
    DB::table('branches')->insert([
        'id' => $branchId,
        'name' => 'Sede Original',
        'slug' => 'sede-original',
        'city' => 'La Paz',
        'location' => DB::raw("ST_GeomFromText('POINT(-68.12 -16.50)')"),
        'coverage_polygon' => DB::raw("ST_GeomFromText('POLYGON((-68.15 -16.48, -68.10 -16.48, -68.10 -16.52, -68.15 -16.52, -68.15 -16.48))')"),
        'delivery_base_fee' => 10.00,
        'delivery_price_per_km' => 4.00,
        'surge_multiplier' => 1.50,
        'min_order_amount' => 100.00,
        'small_order_fee' => 15.00,
        'base_service_fee_percentage' => 10.00,
        'is_default' => false,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $updatePayload = [
        'name' => 'Sede Modificada',
        'city' => 'Cochabamba',
        'phone' => '44556677',
        'address' => 'Av. América Este #450',
        'latitude' => -17.37000,
        'longitude' => -66.16000,
        'coverage_polygon' => [
            [-66.22, -17.31],
            [-66.10, -17.31],
            [-66.10, -17.43],
            [-66.22, -17.43],
            [-66.22, -17.31]
        ],
        'is_active' => true,
        'is_default' => false,
        'delivery_base_fee' => 6.00,
        'delivery_price_per_km' => 2.00,
        'surge_multiplier' => 1.00,
        'min_order_amount' => 35.00,
        'small_order_fee' => 5.00,
        'base_service_fee_percentage' => 5.00,
    ];

    $response = $this->actingAs($superAdmin, 'super_admin')
        ->from(route('admin.operations.branches.edit', ['branch' => $branchId]))
        ->put(route('admin.operations.branches.update', ['branch' => $branchId]), $updatePayload);

    $response->assertStatus(302);
    $response->assertRedirect(route('admin.operations.branches.index'));

    $this->assertDatabaseHas('branches', [
        'id' => $branchId,
        'name' => 'Sede Modificada',
        'slug' => 'sede-modificada',
        'city' => 'Cochabamba',
        'delivery_base_fee' => 6.00,
    ]);
});



test('un administrador que no posee el rol super_admin tiene el acceso estrictamente denegado a la mutacion de sucursales', function () {
    $unauthorizedAdmin = Admin::factory()->create(['is_active' => true]);

    $payload = [
        'name' => 'Intrusion Branch',
        'city' => 'Santa Cruz',
        'latitude' => -17.76900,
        'longitude' => -63.19500,
        'coverage_polygon' => [[-63.25, -17.70], [-63.13, -17.70], [-63.13, -17.84], [-63.25, -17.84], [-63.25, -17.70]],
        'is_active' => true,
        'is_default' => false,
        'delivery_base_fee' => 5.00,
        'delivery_price_per_km' => 1.80,
        'surge_multiplier' => 1.00,
        'min_order_amount' => 40.00,
        'small_order_fee' => 5.00,
        'base_service_fee_percentage' => 5.00,
    ];

    $response = $this->actingAs($unauthorizedAdmin, 'super_admin')
        ->post(route('admin.operations.branches.store'), $payload);

    $response->assertStatus(403);
    $this->assertDatabaseMissing('branches', ['name' => 'Intrusion Branch']);
});



dataset('payloads_sucursales_invalidas', [
    'nombre ausente' => [['name' => '', 'city' => 'La Paz'], 'name'],
    'coordenadas fuera de rango' => [['latitude' => 95.00, 'longitude' => -190.00], 'latitude'],
    'poligono sin suficientes puntos' => [['coverage_polygon' => [[-68.10, -16.52], [-68.06, -16.52]]], 'coverage_polygon'],
    'valores financieros negativos' => [['delivery_base_fee' => -10.00, 'delivery_price_per_km' => -2.50], 'delivery_base_fee'],
    'porcentaje de servicio excedido' => [['base_service_fee_percentage' => 105.00], 'base_service_fee_percentage'],
]);

test('el contrato tecnico del branch valida estrictamente tipados, nulidades y limites operativos', function (array $invalidData, string $expectedErrorKey) {
    $superAdmin = Admin::factory()->create(['is_active' => true]);
    $superAdmin->assignRole('super_admin');

    // Payload base válido fusionado con el subset destructivo del dataset
    $payload = array_merge([
        'name' => 'Sede Validacion',
        'city' => 'La Paz',
        'latitude' => -16.50,
        'longitude' => -68.12,
        'coverage_polygon' => [[-68.15, -16.48], [-68.10, -16.48], [-68.10, -16.52], [-68.15, -16.52], [-68.15, -16.48]],
        'is_active' => true,
        'is_default' => false,
        'delivery_base_fee' => 5.00,
        'delivery_price_per_km' => 2.00,
        'surge_multiplier' => 1.00,
        'min_order_amount' => 20.00,
        'small_order_fee' => 2.00,
        'base_service_fee_percentage' => 5.00,
    ], $invalidData);

    $response = $this->actingAs($superAdmin, 'super_admin')
        ->from(route('admin.operations.branches.create'))
        ->post(route('admin.operations.branches.store'), $payload);

    $response->assertStatus(302);
    $response->assertRedirect(route('admin.operations.branches.create'));
    $response->assertSessionHasErrors($expectedErrorKey);
})->with('payloads_sucursales_invalidas');

test('no se permite duplicidad de nombres en la creacion de sucursales activas', function () {
    $superAdmin = Admin::factory()->create(['is_active' => true]);
    $superAdmin->assignRole('super_admin');

    // Registrar sucursal existente que colisionará por nombre
    DB::table('branches')->insert([
        'id' => Str::uuid()->toString(),
        'name' => 'Sede Duplicada',
        'slug' => 'sede-duplicada',
        'city' => 'La Paz',
        'location' => DB::raw("ST_GeomFromText('POINT(-68.12 -16.50)')"),
        'coverage_polygon' => DB::raw("ST_GeomFromText('POLYGON((-68.15 -16.48, -68.10 -16.48, -68.10 -16.52, -68.15 -16.52, -68.15 -16.48))')"),
        'deleted_epoch' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $payload = [
        'name' => 'Sede Duplicada', // Gatilla la regla unique condicional
        'city' => 'La Paz',
        'latitude' => -16.50,
        'longitude' => -68.12,
        'coverage_polygon' => [[-68.15, -16.48], [-68.10, -16.48], [-68.10, -16.52], [-68.15, -16.52], [-68.15, -16.48]],
        'is_active' => true,
        'is_default' => false,
        'delivery_base_fee' => 5.00,
        'delivery_price_per_km' => 2.00,
        'surge_multiplier' => 1.00,
        'min_order_amount' => 20.00,
        'small_order_fee' => 2.00,
        'base_service_fee_percentage' => 5.00,
    ];

    $response = $this->actingAs($superAdmin, 'super_admin')
        ->from(route('admin.operations.branches.create'))
        ->post(route('admin.operations.branches.store'), $payload);

    $response->assertStatus(302);
    $response->assertSessionHasErrors(['name']);
});



test('el sistema procesa de manera segura entradas de texto con payloads maliciosos de inyeccion en sucursales', function () {
    $superAdmin = Admin::factory()->create(['is_active' => true]);
    $superAdmin->assignRole('super_admin');

    $payload = [
        'name' => "Sede Central' OR 1=1 --",
        'city' => "<script>alert('xss')</script>La Paz",
        'latitude' => -16.50,
        'longitude' => -68.12,
        'coverage_polygon' => [[-68.15, -16.48], [-68.10, -16.48], [-68.10, -16.52], [-68.15, -16.52], [-68.15, -16.48]],
        'is_active' => true,
        'is_default' => false,
        'delivery_base_fee' => 5.00,
        'delivery_price_per_km' => 2.00,
        'surge_multiplier' => 1.00,
        'min_order_amount' => 20.00,
        'small_order_fee' => 2.00,
        'base_service_fee_percentage' => 5.00,
    ];

    $response = $this->actingAs($superAdmin, 'super_admin')
        ->post(route('admin.operations.branches.store'), $payload);

    $response->assertStatus(302);
    
    $this->assertDatabaseHas('branches', [
        'name' => "Sede Central' OR 1=1 --",
        'city' => "<script>alert('xss')</script>La Paz",
    ]);
});