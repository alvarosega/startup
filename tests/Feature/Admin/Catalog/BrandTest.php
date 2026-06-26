<?php

declare(strict_types=1);

/**
 * RUTA DEL ARCHIVO: tests/Feature/Admin/Catalog/BrandTest.php
 * COMANDO DE EJECUCIÓN: ./vendor/bin/pest tests/Feature/Admin/Catalog/BrandTest.php
 */

use App\Models\Users\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');

    // 1. Persistencia estricta de la tabla 'branches' con datos geoespaciales reales
    $this->branchId = (string) Str::uuid7();
    DB::table('branches')->insert([
        'id' => $this->branchId,
        'name' => 'Sucursal Central QA',
        'slug' => 'sucursal-central-qa',
        'city' => 'La Paz',
        'phone' => '+59171234567',
        'address' => 'Av. Arce #1234',
        'location' => DB::raw("ST_PointFromText('POINT(-16.5000 -68.1500)')"),
        'coverage_polygon' => DB::raw("ST_PolygonFromText('POLYGON((-16.5 -68.1, -16.5 -68.2, -16.6 -68.2, -16.6 -68.1, -16.5 -68.1))')"),
        'delivery_base_fee' => 10.00,
        'delivery_price_per_km' => 2.50,
        'surge_multiplier' => 1.00,
        'min_order_amount' => 50.00,
        'small_order_fee' => 5.00,
        'base_service_fee_percentage' => 3.50,
        'is_default' => true,
        'is_active' => true,
        'deleted_epoch' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // 2. Persistencia estricta de la tabla 'categories' basada en su esquema contractual
    $this->categoryId = (string) Str::uuid7();
    DB::table('categories')->insert([
        'id' => $this->categoryId,
        'parent_id' => null,
        'name' => 'Repuestos Hidráulicos',
        'slug' => 'repuestos-hidraulicos',
        'external_code' => 'CAT-HYD-001',
        'deleted_epoch' => 0,
        'tax_classification' => 'STANDARD',
        'requires_age_check' => false,
        'is_active' => true,
        'is_featured' => false,
        'sort_order' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // 3. Proveedor activo por defecto para flujos ordinarios
    $this->activeProviderId = (string) Str::uuid7();
    DB::table('providers')->insert([
        'id' => $this->activeProviderId,
        'company_name' => 'Distribuidora Automotriz Activa S.A.',
        'commercial_name' => 'Dist Auto Activa',
        'slug' => 'distribuidora-automotriz-activa-sa',
        'tax_id' => 'NIT-11223344-0',
        'internal_code' => 'PROV-ACT-01',
        'is_active' => true,
        'deleted_epoch' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // 4. Proveedor INACTIVO para validación explícita de regla de negocio
    $this->inactiveProviderId = (string) Str::uuid7();
    DB::table('providers')->insert([
        'id' => $this->inactiveProviderId,
        'company_name' => 'Importaciones Desactivadas SRL',
        'commercial_name' => 'Imp Desactivadas',
        'slug' => 'importaciones-desactivadas-srl',
        'tax_id' => 'NIT-55667788-9',
        'internal_code' => 'PROV-INA-02',
        'is_active' => false,
        'deleted_epoch' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // 5. Blindaje Absoluto de Caja Negra: Inserción directa vía SQL para evitar restricciones de $fillable en el modelo
    $this->adminId = (string) Str::uuid7();
    DB::table('admins')->insert([
        'id' => $this->adminId,
        'first_name' => 'QA Lead',
        'last_name' => 'Automation Engineer',
        'phone' => '+59170000000',
        'branch_id' => $this->branchId,
        'email' => 'superadmin-test@platform.com',
        'password' => bcrypt('StrictSecurityPassword2026*'),
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Hidratación del modelo desde el registro real de la base de datos para simular la sesión
    $this->adminUser = Admin::find($this->adminId);
});

/*
|--------------------------------------------------------------------------
| CAPA DE SEGURIDAD Y MIDDLEWARE DE ACCESO (401 / 403)
|--------------------------------------------------------------------------
*/

test('unauthenticated request to brand creation returns a redirection to login', function () {
    $response = $this->post('/catalog/brands', [
        'name' => 'Unauthenticated Brand',
    ]);

    $response->assertStatus(302);
    $response->assertRedirect(route('login'));
});

test('authenticated user using non-authorized guard receives a forbidden response', function () {
    $response = $this->actingAs($this->adminUser, 'customer')
        ->postJson('/catalog/brands', [
            'name' => 'Unauthorized Post',
        ]);

    $response->assertStatus(403);
});

/*
|--------------------------------------------------------------------------
| FLUJOS DE CREACIÓN (STORE) - REGLAS DE NEGOCIO Y ÉXITO
|--------------------------------------------------------------------------
*/

test('super admin can successfully create a brand with a complete contract payload', function () {
    $imageFile = UploadedFile::fake()->image('brand_identity.png')->size(1500);

    $payload = [
        'name' => 'Brembo Premium',
        'slug' => 'brembo-premium',
        'provider_id' => $this->activeProviderId,
        'category_id' => $this->categoryId,
        'website' => 'https://brembo.com',
        'image' => $imageFile,
        'is_active' => true,
        'is_featured' => true,
        'description' => 'Sistemas de frenado de alto rendimiento.',
        'bg_color' => '#FF0000',
    ];

    $response = $this->actingAs($this->adminUser, 'super_admin')
        ->post('/catalog/brands', $payload);

    $response->assertStatus(302);

    $this->assertDatabaseHas('brands', [
        'name' => 'Brembo Premium',
        'slug' => 'brembo-premium',
        'provider_id' => $this->activeProviderId,
        'category_id' => $this->categoryId,
        'website' => 'https://brembo.com',
        'is_active' => true,
        'is_featured' => true,
        'description' => 'Sistemas de frenado de alto rendimiento.',
        'bg_color' => '#FF0000',
        'deleted_epoch' => 0,
    ]);
});

test('brand creation allows association with an inactive provider according to business rule', function () {
    $payload = [
        'name' => 'Bosch Automotive',
        'slug' => 'bosch-automotive',
        'provider_id' => $this->inactiveProviderId,
        'category_id' => $this->categoryId,
        'is_active' => true,
        'is_featured' => false,
    ];

    $response = $this->actingAs($this->adminUser, 'super_admin')
        ->post('/catalog/brands', $payload);

    $response->assertStatus(302);

    $this->assertDatabaseHas('brands', [
        'name' => 'Bosch Automotive',
        'provider_id' => $this->inactiveProviderId,
    ]);
});

test('sort_order defaults to 1 when creating a brand in a completely empty table', function () {
    $payload = [
        'name' => 'Initial Brand Test',
        'provider_id' => $this->activeProviderId,
        'category_id' => $this->categoryId,
        'is_active' => true,
        'is_featured' => false,
    ];

    $response = $this->actingAs($this->adminUser, 'super_admin')
        ->post('/catalog/brands', $payload);

    $response->assertStatus(302);

    $this->assertDatabaseHas('brands', [
        'name' => 'Initial Brand Test',
        'sort_order' => 1,
    ]);
});

test('sort_order automatically increments based on global maximum value plus one when omitted', function () {
    DB::table('brands')->insert([
        'id' => (string) Str::uuid7(),
        'name' => 'Base Brand A',
        'slug' => 'base-brand-a',
        'provider_id' => $this->activeProviderId,
        'category_id' => $this->categoryId,
        'sort_order' => 99,
        'is_active' => true,
        'is_featured' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $payload = [
        'name' => 'Incremental Brand B',
        'provider_id' => $this->activeProviderId,
        'category_id' => $this->categoryId,
        'is_active' => true,
        'is_featured' => false,
    ];

    $response = $this->actingAs($this->adminUser, 'super_admin')
        ->post('/catalog/brands', $payload);

    $response->assertStatus(302);

    $this->assertDatabaseHas('brands', [
        'name' => 'Incremental Brand B',
        'sort_order' => 100,
    ]);
});

test('slug is inferred and formatted automatically from name if omitted from the payload', function () {
    $payload = [
        'name' => 'ACDelco Gold Parts  ',
        'provider_id' => $this->activeProviderId,
        'category_id' => $this->categoryId,
        'is_active' => true,
        'is_featured' => false,
    ];

    $response = $this->actingAs($this->adminUser, 'super_admin')
        ->post('/catalog/brands', $payload);

    $response->assertStatus(302);

    $this->assertDatabaseHas('brands', [
        'slug' => 'acdelco-gold-parts',
    ]);
});

/*
|--------------------------------------------------------------------------
| FLUJOS DE VALIDACIÓN E INTEGRIDAD DE DATOS (422)
|--------------------------------------------------------------------------
*/

test('store process fails validation when mandatory payload keys are empty', function () {
    $response = $this->actingAs($this->adminUser, 'super_admin')
        ->postJson('/catalog/brands', [
            'name' => '',
            'provider_id' => '',
            'category_id' => '',
            'is_active' => null,
            'is_featured' => null,
        ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['name', 'provider_id', 'category_id', 'is_active', 'is_featured']);
});

test('store validation rejects structural anomalies and out-of-bound variables', function () {
    $response = $this->actingAs($this->adminUser, 'super_admin')
        ->postJson('/catalog/brands', [
            'name' => str_repeat('A', 101),
            'slug' => str_repeat('s', 121),
            'website' => 'ftp://invalid-web-format',
            'bg_color' => '#FF000',
            'provider_id' => (string) Str::uuid7(),
            'category_id' => (string) Str::uuid7(),
        ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['name', 'slug', 'website', 'bg_color', 'provider_id', 'category_id']);
});

test('store validation rejects a slug that matches an existing active brand', function () {
    DB::table('brands')->insert([
        'id' => (string) Str::uuid7(),
        'name' => 'Active Line',
        'slug' => 'active-line',
        'provider_id' => $this->activeProviderId,
        'category_id' => $this->categoryId,
        'deleted_epoch' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($this->adminUser, 'super_admin')
        ->postJson('/catalog/brands', [
            'name' => 'Active Line Alternative',
            'slug' => 'active-line',
            'provider_id' => $this->activeProviderId,
            'category_id' => $this->categoryId,
            'is_active' => true,
            'is_featured' => false,
        ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['slug']);
});

test('store validation rejects a slug matching an archived brand returning the exact explicit alert message', function () {
    DB::table('brands')->insert([
        'id' => (string) Str::uuid7(),
        'name' => 'Old Legacy Brand',
        'slug' => 'old-legacy-brand',
        'provider_id' => $this->activeProviderId,
        'category_id' => $this->categoryId,
        'deleted_epoch' => 1719350400,
        'deleted_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($this->adminUser, 'super_admin')
        ->postJson('/catalog/brands', [
            'name' => 'New Rebuilt Line',
            'slug' => 'old-legacy-brand',
            'provider_id' => $this->activeProviderId,
            'category_id' => $this->categoryId,
            'is_active' => true,
            'is_featured' => false,
        ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['slug']);
    
    $errorArray = $response->json('errors.slug');
    expect($errorArray[0])->toBe('la marca fue eliminada, desea volverla a crear?');
});

/*
|--------------------------------------------------------------------------
| FLUJOS DE ACTUALIZACIÓN (UPDATE) - REGLAS DE NEGOCIO RESTRICTIVAS
|--------------------------------------------------------------------------
*/

test('super admin can completely update a brand configuration without slug collisions with itself', function () {
    $brandId = (string) Str::uuid7();
    DB::table('brands')->insert([
        'id' => $brandId,
        'name' => 'Castrol Oil',
        'slug' => 'castrol-oil',
        'provider_id' => $this->activeProviderId,
        'category_id' => $this->categoryId,
        'is_active' => true,
        'is_featured' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $payload = [
        'name' => 'Castrol Edge Ultra',
        'slug' => 'castrol-oil',
        'provider_id' => $this->activeProviderId,
        'category_id' => $this->categoryId,
        'website' => 'https://castrol.com/edge',
        'is_active' => true,
        'is_featured' => true,
        'description' => 'Nueva fórmula de lubricantes.',
        'bg_color' => '#00FF00',
    ];

    $response = $this->actingAs($this->adminUser, 'super_admin')
        ->put("/catalog/brands/{$brandId}", $payload);

    $response->assertStatus(302);

    $this->assertDatabaseHas('brands', [
        'id' => $brandId,
        'name' => 'Castrol Edge Ultra',
        'slug' => 'castrol-oil',
        'is_featured' => true,
        'bg_color' => '#00FF00',
    ]);
});

test('update validation fails if a brand attempts to point to itself as parent_id', function () {
    $brandId = (string) Str::uuid7();
    DB::table('brands')->insert([
        'id' => $brandId,
        'name' => 'Cyclic Brand',
        'slug' => 'cyclic-brand',
        'provider_id' => $this->activeProviderId,
        'category_id' => $this->categoryId,
        'parent_id' => null,
        'is_active' => true,
        'is_featured' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $payload = [
        'name' => 'Cyclic Brand Modified',
        'slug' => 'cyclic-brand',
        'provider_id' => $this->activeProviderId,
        'category_id' => $this->categoryId,
        'is_active' => true,
        'is_featured' => false,
        'parent_id' => $brandId,
    ];

    $response = $this->actingAs($this->adminUser, 'super_admin')
        ->putJson("/catalog/brands/{$brandId}", $payload);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['parent_id']);
    expect($response->json('errors.parent_id.0'))->toBe('Operación inválida: Una marca no puede ser sub-marca de sí misma.');
});

test('update validation blocks execution if a brand with existing children attempts to descend a level', function () {
    $currentParentId = (string) Str::uuid7();
    $childId = (string) Str::uuid7();
    $targetParentId = (string) Str::uuid7();

    DB::table('brands')->insert([
        [
            'id' => $targetParentId,
            'name' => 'Nueva Marca Raiz Destino',
            'slug' => 'nueva-marca-raiz-destino',
            'provider_id' => $this->activeProviderId,
            'category_id' => $this->categoryId,
            'parent_id' => null,
            'is_active' => true,
            'is_featured' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => $currentParentId,
            'name' => 'Marca Raiz Actual Con Hijos',
            'slug' => 'marca-raiz-actual-con-hijos',
            'provider_id' => $this->activeProviderId,
            'category_id' => $this->categoryId,
            'parent_id' => null,
            'is_active' => true,
            'is_featured' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => $childId,
            'name' => 'Marca Hija Dependiente',
            'slug' => 'marca-hija-dependiente',
            'provider_id' => $this->activeProviderId,
            'category_id' => $this->categoryId,
            'parent_id' => $currentParentId,
            'is_active' => true,
            'is_featured' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]
    ]);

    $payload = [
        'name' => 'Intento Cambio Estructural',
        'slug' => 'marca-raiz-actual-con-hijos',
        'provider_id' => $this->activeProviderId,
        'category_id' => $this->categoryId,
        'is_active' => true,
        'is_featured' => false,
        'parent_id' => $targetParentId,
    ];

    $response = $this->actingAs($this->adminUser, 'super_admin')
        ->putJson("/catalog/brands/{$currentParentId}", $payload);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['parent_id']);
    expect($response->json('errors.parent_id.0'))->toBe('Restricción de nivel: Esta marca posee sub-marcas asignadas y no puede descender de nivel.');
});