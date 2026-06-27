<?php

declare(strict_types=1);

use App\Models\Users\Admin;
use App\Models\Catalog\Category;
use App\Models\Catalog\Brand;
use App\Models\Catalog\Product;
use App\Models\Catalog\Sku;
use App\Models\Inventory\Price;
use App\Models\Inventory\InventoryLot;
use App\Models\Operations\Provider;
use App\Models\Operations\Branch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    \Inertia\Inertia::version('testing');
    
    $this->superAdminRole = Role::create([
        'name' => 'super_admin',
        'guard_name' => 'super_admin'
    ]);

    $this->user = Admin::create([
        'id' => '018cbf91-9b71-7000-8000-000000000001',
        'first_name' => 'Super',
        'last_name' => 'Admin',
        'email' => 'admin@test.com',
        'password' => bcrypt('password'),
        'is_active' => true,
    ]);

    $this->user->assignRole($this->superAdminRole);

    $this->branch = Branch::create([
        'id' => '018cbf91-9b71-7000-8000-000000000005',
        'name' => 'Sucursal Central',
        'slug' => 'sucursal-central',
        'location' => DB::raw("ST_PointFromText('POINT(-16.5000 -68.1500)')"),
        'coverage_polygon' => DB::raw("ST_PolygonFromText('POLYGON(( -16.4 -68.2, -16.4 -68.1, -16.6 -68.1, -16.6 -68.2, -16.4 -68.2 ))')"),
        'is_active' => true,
    ]);

    $this->category = Category::create([
        'id' => '018cbf91-9b71-7000-8000-000000000010',
        'name' => 'Licores',
        'slug' => 'licores',
        'requires_age_check' => true,
        'is_active' => true,
        'is_featured' => false,
    ]);

    $this->provider = Provider::create([
        'id' => '018cbf91-9b71-7000-8000-000000000011',
        'company_name' => 'Corporación del Sur',
        'slug' => 'corporacion-del-sur',
        'tax_id' => 'NIT-9981273',
        'is_active' => true,
    ]);

    $this->brand = Brand::create([
        'id' => '018cbf91-9b71-7000-8000-000000000012',
        'name' => 'Ron Abuelo',
        'slug' => 'ron-abuelo',
        'provider_id' => $this->provider->id,
        'category_id' => $this->category->id,
        'is_active' => true,
        'is_featured' => false,
    ]);
});

dataset('invalid_product_payloads', [
    'missing_name' => [
        fn($brandId, $catId) => [
            'name' => '', 'brand_id' => $brandId, 'category_id' => $catId,
            'is_active' => true, 'is_alcoholic' => true, 'idempotency_key' => '018cbf91-9b71-7000-8000-000000009999'
        ],
        'name'
    ],
    'missing_brand' => [
        fn($brandId, $catId) => [
            'name' => 'Ron Centenario', 'brand_id' => '', 'category_id' => $catId,
            'is_active' => true, 'is_alcoholic' => true, 'idempotency_key' => '018cbf91-9b71-7000-8000-000000009999'
        ],
        'brand_id'
    ],
    'invalid_idempotency_key' => [
        fn($brandId, $catId) => [
            'name' => 'Ron Centenario', 'brand_id' => $brandId, 'category_id' => $catId,
            'is_active' => true, 'is_alcoholic' => true, 'idempotency_key' => 'not-a-uuid'
        ],
        'idempotency_key'
    ],
]);

test('guest access to catalog items redirects to login system parameters', function () {
    $this->get('/adm/catalog/products')->assertRedirect('adm/login');
    $this->post('/adm/catalog/products', [])->assertRedirect('adm/login');
});

test('non administrative users execution attempts yield formal 403 blocks', function () {
    $regularAdmin = Admin::create([
        'id' => '018cbf91-9b71-7000-8000-000000000002',
        'first_name' => 'Operator',
        'last_name' => 'One',
        'email' => 'operator@test.com',
        'password' => bcrypt('password'),
        'is_active' => true,
    ]);

    $this->actingAs($regularAdmin, 'super_admin')
        ->get('/adm/catalog/products')
        ->assertStatus(403);
});

test('product catalog inventory page delivers precise matrix view workspace components', function () {
    $this->actingAs($this->user, 'super_admin')
        ->get('/adm/catalog/products')
        ->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Catalog/Products/Index')
            ->has('products')
        );
});

test('product sequencing controls open accurate reorder workflow pages', function () {
    $this->actingAs($this->user, 'super_admin')
        ->get('/adm/catalog/products/reorder')
        ->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Catalog/Products/Reorder')
        );
});

test('successful creation tracking automatically calculates target model slug fields', function () {
    $payload = [
        'name' => 'Ron Añejo Ultra',
        'brand_id' => $this->brand->id,
        'category_id' => $this->category->id,
        'description' => 'Ron premium añejado en roble',
        'is_active' => true,
        'is_alcoholic' => true,
        'idempotency_key' => '018cbf91-9b71-7000-8000-000000007777',
    ];

    $this->actingAs($this->user, 'super_admin')
        ->post('/adm/catalog/products', $payload)
        ->assertRedirect();

    $this->assertDatabaseHas('products', [
        'name' => 'Ron Añejo Ultra',
        'slug' => 'ron-anejo-ultra',
        'brand_id' => $this->brand->id,
        'category_id' => $this->category->id,
        'is_alcoholic' => true,
        'deleted_epoch' => 0,
    ]);
});

test('idempotency token collisions enforce strict validation rejections avoiding duplicate records', function () {
    $payload = [
        'name' => 'Unique Rum Batch',
        'brand_id' => $this->brand->id,
        'category_id' => $this->category->id,
        'is_active' => true,
        'is_alcoholic' => true,
        'idempotency_key' => '018cbf91-9b71-7000-8000-000000005555',
    ];

    $this->actingAs($this->user, 'super_admin')
        ->post('/adm/catalog/products', $payload)
        ->assertRedirect();

    $this->actingAs($this->user, 'super_admin')
        ->post('/adm/catalog/products', $payload)
        ->assertStatus(302)
        ->assertSessionHasErrors(['idempotency_key']);
});

test('active identical slug allocations collide returning distinct cross field constraints', function () {
    Product::create([
        'id' => '018cbf91-9b71-7000-8000-000000000888',
        'brand_id' => $this->brand->id,
        'category_id' => $this->category->id,
        'name' => 'Conflicting Product',
        'slug' => 'conflicting-product',
        'is_active' => true,
    ]);

    $payload = [
        'name' => 'Conflicting Product',
        'brand_id' => $this->brand->id,
        'category_id' => $this->category->id,
        'is_active' => true,
        'is_alcoholic' => false,
        'idempotency_key' => '018cbf91-9b71-7000-8000-000000001234',
    ];

    $this->actingAs($this->user, 'super_admin')
        ->post('/adm/catalog/products', $payload)
        ->assertStatus(302)
        ->assertSessionHasErrors(['name', 'slug']);
});

test('validation field pool testing matrix executions', function (Closure $data, string $field) {
    $this->actingAs($this->user, 'super_admin')
        ->post('/adm/catalog/products', $data($this->brand->id, $this->category->id))
        ->assertStatus(302)
        ->assertSessionHasErrors([$field]);
})->with('invalid_product_payloads');

test('bulk insertion creates stock records linked directly to active parent endpoints', function () {
    $product = Product::create([
        'id' => '018cbf91-9b71-7000-8000-000000000050',
        'brand_id' => $this->brand->id,
        'category_id' => $this->category->id,
        'name' => 'Product For Bulk Sku',
        'slug' => 'product-for-bulk-sku',
        'is_active' => true,
    ]);

    $payload = [
        'skus' => [
            [
                'name' => '750ml Premium Bottle',
                'code' => 'SKU-750-PREM',
                'base_price' => 150.00,
                'conversion_factor' => 1.000,
                'weight' => 1.250,
                'is_active' => true,
            ],
            [
                'name' => '1000ml Economy Pack',
                'code' => 'SKU-1000-ECON',
                'base_price' => 190.00,
                'conversion_factor' => 1.333,
                'weight' => 1.600,
                'is_active' => true,
            ]
        ]
    ];

    $this->actingAs($this->user, 'super_admin')
        ->post("/adm/catalog/products/{$product->id}/skus", $payload)
        ->assertRedirect();

    $this->assertDatabaseHas('skus', [
        'product_id' => $product->id,
        'code' => 'SKU-750-PREM',
        'base_price' => 150.00,
    ]);

    $this->assertDatabaseHas('skus', [
        'product_id' => $product->id,
        'code' => 'SKU-1000-ECON',
        'base_price' => 190.00,
    ]);
});

test('null identifiers inside bulk operations auto generate distinct code records', function () {
    $product = Product::create([
        'id' => '018cbf91-9b71-7000-8000-000000000060',
        'brand_id' => $this->brand->id,
        'category_id' => $this->category->id,
        'name' => 'Auto Code Product',
        'slug' => 'auto-code-product',
        'is_active' => true,
    ]);

    $payload = [
        'skus' => [
            [
                'name' => 'Implicit Token Sku',
                'code' => null,
                'base_price' => 85.50,
                'conversion_factor' => 1.000,
                'weight' => 0.900,
                'is_active' => true,
            ]
        ]
    ];

    $this->actingAs($this->user, 'super_admin')
        ->post("/adm/catalog/products/{$product->id}/skus", $payload)
        ->assertRedirect();

    $skuRecord = Sku::where('product_id', $product->id)->first();
    expect($skuRecord->code)->not->toBeNull();
});

test('sku update modification operations successfully persist mechanical properties', function () {
    $product = Product::create([
        'id' => '018cbf91-9b71-7000-8000-000000000070',
        'brand_id' => $this->brand->id,
        'category_id' => $this->category->id,
        'name' => 'Sku Parent',
        'slug' => 'sku-parent',
        'is_active' => true,
    ]);

    $sku = Sku::create([
        'id' => '018cbf91-9b71-7000-8000-000000000071',
        'product_id' => $product->id,
        'name' => 'Original Sku Name',
        'code' => 'FIXED-CODE-99',
        'base_price' => 45.00,
        'weight' => 0.500,
        'conversion_factor' => 1.000,
        'is_active' => true,
    ]);

    $payload = [
        'name' => 'Mutated Sku Name',
        'code' => 'FIXED-CODE-99',
        'base_price' => 50.00,
        'weight' => 0.550,
        'conversion_factor' => 1.100,
        'is_active' => true,
    ];

    $this->actingAs($this->user, 'super_admin')
        ->put("/adm/catalog/skus/{$sku->id}", $payload)
        ->assertRedirect();

    $this->assertDatabaseHas('skus', [
        'id' => $sku->id,
        'name' => 'Mutated Sku Name',
        'base_price' => 50.00,
        'weight' => 0.550,
    ]);
});

test('sku code altering attempts trigger 422 errors due to architecture immutability constraints', function () {
    $product = Product::create([
        'id' => '018cbf91-9b71-7000-8000-000000000080',
        'brand_id' => $this->brand->id,
        'category_id' => $this->category->id,
        'name' => 'Immutable Parent',
        'slug' => 'immutable-parent',
        'is_active' => true,
    ]);

    $sku = Sku::create([
        'id' => '018cbf91-9b71-7000-8000-000000000081',
        'product_id' => $product->id,
        'name' => 'Immutable Sku',
        'code' => 'PERMANENT-123',
        'base_price' => 10.00,
        'weight' => 1.000,
        'conversion_factor' => 1.000,
        'is_active' => true,
    ]);

    $payload = [
        'name' => 'Immutable Sku',
        'code' => 'FRAUDULENT-MUTATION-999',
        'base_price' => 10.00,
        'weight' => 1.000,
        'conversion_factor' => 1.000,
        'is_active' => true,
    ];

    $this->actingAs($this->user, 'super_admin')
        ->put("/adm/catalog/skus/{$sku->id}", $payload)
        ->assertStatus(302)
        ->assertSessionHasErrors(['code']);
});

test('active dependent inventory variants block root product soft deletion sequences', function () {
    $product = Product::create([
        'id' => '018cbf91-9b71-7000-8000-000000000090',
        'brand_id' => $this->brand->id,
        'category_id' => $this->category->id,
        'name' => 'Locked Product Unit',
        'slug' => 'locked-product-unit',
        'is_active' => true,
    ]);

    Sku::create([
        'id' => '018cbf91-9b71-7000-8000-000000000091',
        'product_id' => $product->id,
        'name' => 'Active Dependent Sku',
        'code' => 'DEP-SKU-91',
        'base_price' => 20.00,
        'weight' => 1.000,
        'conversion_factor' => 1.000,
        'is_active' => true,
    ]);

    $this->actingAs($this->user, 'super_admin')
        ->delete("/adm/catalog/products/{$product->id}")
        ->assertStatus(302)
        ->assertSessionHasErrors(['product']);

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'deleted_at' => null,
    ]);
});

test('associated commercial values inside prices table restrict specific sku removals', function () {
    $product = Product::create([
        'id' => '018cbf91-9b71-7000-8000-000000000210',
        'brand_id' => $this->brand->id,
        'category_id' => $this->category->id,
        'name' => 'Sku Isolation Testing',
        'slug' => 'sku-isolation-testing',
        'is_active' => true,
    ]);

    $sku = Sku::create([
        'id' => '018cbf91-9b71-7000-8000-000000000211',
        'product_id' => $product->id,
        'name' => 'Valued Stock Item',
        'code' => 'VALUED-VAL',
        'base_price' => 300.00,
        'weight' => 2.000,
        'conversion_factor' => 1.000,
        'is_active' => true,
    ]);

    Price::create([
        'id' => '018cbf91-9b71-7000-8000-000000000212',
        'sku_id' => $sku->id,
        'branch_id' => $this->branch->id,
        'type' => 'regular',
        'list_price' => 320.00,
        'final_price' => 300.00,
        'min_quantity' => 1,
        'priority' => 1,
        'deleted_epoch' => 0,
        'valid_from' => now(),
    ]);

    $this->actingAs($this->user, 'super_admin')
        ->delete("/adm/catalog/skus/{$sku->id}")
        ->assertStatus(302)
        ->assertSessionHasErrors(['sku']);

    $this->assertDatabaseHas('skus', [
        'id' => $sku->id,
        'deleted_at' => null,
    ]);
});

test('clean isolated models mutate epoch indicators successfully matching standard purge cycles', function () {
    $product = Product::create([
        'id' => '018cbf91-9b71-7000-8000-000000000550',
        'brand_id' => $this->brand->id,
        'category_id' => $this->category->id,
        'name' => 'Purgeable Asset',
        'slug' => 'purgeable-asset',
        'is_active' => true,
        'deleted_epoch' => 0,
    ]);

    $this->actingAs($this->user, 'super_admin')
        ->delete("/adm/catalog/products/{$product->id}")
        ->assertRedirect();

    $this->assertSoftDeleted('products', [
        'id' => $product->id,
    ]);

    $record = Product::withTrashed()->find($product->id);
    expect($record->deleted_epoch)->toBeGreaterThan(0);
});