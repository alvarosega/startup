<?php

declare(strict_types=1);

use App\Models\Users\Admin;
use App\Models\Catalog\Category;
use App\Models\Catalog\Brand;
use App\Models\Catalog\Product;
use App\Models\Operations\Provider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    
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

    $this->defaultCategory = Category::create([
        'id' => '018cbf91-9b71-7000-8000-000000000010',
        'name' => 'Bebidas',
        'slug' => 'bebidas',
        'requires_age_check' => false,
        'is_active' => true,
        'is_featured' => false,
    ]);

    $this->defaultProvider = Provider::create([
        'id' => '018cbf91-9b71-7000-8000-000000000011',
        'company_name' => 'Proveedor Central S.A.',
        'slug' => 'proveedor-central-sa',
        'tax_id' => 'NIT-123456789',
        'is_active' => true,
    ]);
});

dataset('invalid_brand_payloads', [
    'missing_name' => [
        fn($providerId, $categoryId) => [
            'name' => '', 'provider_id' => $providerId, 'category_id' => $categoryId,
            'is_active' => true, 'is_featured' => false
        ],
        'name'
    ],
    'name_exceeds_max' => [
        fn($providerId, $categoryId) => [
            'name' => str_repeat('b', 101), 'provider_id' => $providerId, 'category_id' => $categoryId,
            'is_active' => true, 'is_featured' => false
        ],
        'name'
    ],
    'invalid_website_url' => [
        fn($providerId, $categoryId) => [
            'name' => 'Brand Corp', 'provider_id' => $providerId, 'category_id' => $categoryId,
            'website' => 'not-a-valid-url', 'is_active' => true, 'is_featured' => false
        ],
        'website'
    ],
    'malformed_bg_hex_color' => [
        fn($providerId, $categoryId) => [
            'name' => 'Brand Corp', 'provider_id' => $providerId, 'category_id' => $categoryId,
            'bg_color' => '#FFFF000', 'is_active' => true, 'is_featured' => false
        ],
        'bg_color'
    ],
    'non_existent_provider' => [
        fn($providerId, $categoryId) => [
            'name' => 'Brand Corp', 'provider_id' => '018cbf91-9b71-7000-8000-999999999999', 'category_id' => $categoryId,
            'is_active' => true, 'is_featured' => false
        ],
        'provider_id'
    ],
]);

test('guest access to brand endpoints redirects to login', function () {
    $this->get('/adm/catalog/brands')->assertRedirect('adm/login');
    $this->post('/adm/catalog/brands', [])->assertRedirect('adm/login');
});

test('unauthorized users without super_admin role receive 403 status code', function () {
    $regularAdmin = Admin::create([
        'id' => '018cbf91-9b71-7000-8000-000000000002',
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'jane@test.com',
        'password' => bcrypt('password'),
        'is_active' => true,
    ]);

    $this->actingAs($regularAdmin, 'super_admin')
        ->get('/adm/catalog/brands')
        ->assertStatus(403);
});

test('super admin can render brand index view component interface', function () {
    Brand::create([
        'id' => '018cbf91-9b71-7000-8000-000000000200',
        'name' => 'Coca Cola',
        'slug' => 'coca-cola',
        'provider_id' => $this->defaultProvider->id,
        'category_id' => $this->defaultCategory->id,
        'is_active' => true,
        'is_featured' => true,
    ]);

    $this->actingAs($this->user, 'super_admin')
        ->get('/adm/catalog/brands')
        ->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Catalog/Brands/Index')
            ->has('brands')
        );
});

test('super admin can render brand creation interface layout', function () {
    $this->actingAs($this->user, 'super_admin')
        ->get('/adm/catalog/brands/create')
        ->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Catalog/Brands/Create')
        );
});

test('super admin can store a root brand structure with automatic slug generation', function () {
    $payload = [
        'name' => 'Pepsi Co',
        'provider_id' => $this->defaultProvider->id,
        'category_id' => $this->defaultCategory->id,
        'website' => 'https://pepsi.com',
        'is_active' => true,
        'is_featured' => true,
        'bg_color' => '#0000FF',
        'description' => 'Bebidas gasificadas refrescantes',
    ];

    $this->actingAs($this->user, 'super_admin')
        ->post('/adm/catalog/brands', $payload)
        ->assertRedirect();

    $this->assertDatabaseHas('brands', [
        'name' => 'Pepsi Co',
        'slug' => 'pepsi-co',
        'parent_id' => null,
        'provider_id' => $this->defaultProvider->id,
        'category_id' => $this->defaultCategory->id,
        'deleted_epoch' => 0,
        'bg_color' => '#0000FF',
    ]);
});

test('super admin can store sub brand hierarchy under a live root brand record', function () {
    $rootBrand = Brand::create([
        'id' => '018cbf91-9b71-7000-8000-000000000300',
        'name' => 'Nestle',
        'slug' => 'nestle',
        'provider_id' => $this->defaultProvider->id,
        'category_id' => $this->defaultCategory->id,
        'is_active' => true,
        'is_featured' => false,
    ]);

    $payload = [
        'name' => 'Nestle Pure Life',
        'parent_id' => $rootBrand->id,
        'provider_id' => $this->defaultProvider->id,
        'category_id' => $this->defaultCategory->id,
        'is_active' => true,
        'is_featured' => false,
    ];

    $this->actingAs($this->user, 'super_admin')
        ->post('/adm/catalog/brands', $payload)
        ->assertRedirect();

    $this->assertDatabaseHas('brands', [
        'name' => 'Nestle Pure Life',
        'parent_id' => $rootBrand->id,
        'slug' => 'nestle-pure-life',
    ]);
});

test('storing a third tier brand tree deep level aborts with validation errors', function () {
    $rootBrand = Brand::create([
        'id' => '018cbf91-9b71-7000-8000-000000000400',
        'name' => 'Root Holding',
        'slug' => 'root-holding',
        'provider_id' => $this->defaultProvider->id,
        'category_id' => $this->defaultCategory->id,
        'is_active' => true,
        'is_featured' => false,
    ]);

    $subBrand = Brand::create([
        'id' => '018cbf91-9b71-7000-8000-000000000401',
        'name' => 'Mid Division',
        'slug' => 'mid-division',
        'parent_id' => $rootBrand->id,
        'provider_id' => $this->defaultProvider->id,
        'category_id' => $this->defaultCategory->id,
        'is_active' => true,
        'is_featured' => false,
    ]);

    $payload = [
        'name' => 'Deep Level Failure',
        'parent_id' => $subBrand->id,
        'provider_id' => $this->defaultProvider->id,
        'category_id' => $this->defaultCategory->id,
        'is_active' => true,
        'is_featured' => false,
    ];

    $this->actingAs($this->user, 'super_admin')
        ->post('/adm/catalog/brands', $payload)
        ->assertStatus(302)
        ->assertSessionHasErrors(['parent_id']);
});

test('active identical slug criteria triggers unique validation failure', function () {
    Brand::create([
        'id' => '018cbf91-9b71-7000-8000-000000000500',
        'name' => 'Cerveza Paceña',
        'slug' => 'cerveza-pacena',
        'provider_id' => $this->defaultProvider->id,
        'category_id' => $this->defaultCategory->id,
        'is_active' => true,
        'is_featured' => false,
        'deleted_epoch' => 0,
    ]);

    $payload = [
        'name' => 'Paceña Tradicional',
        'slug' => 'cerveza-pacena',
        'provider_id' => $this->defaultProvider->id,
        'category_id' => $this->defaultCategory->id,
        'is_active' => true,
        'is_featured' => false,
    ];

    $this->actingAs($this->user, 'super_admin')
        ->post('/adm/catalog/brands', $payload)
        ->assertStatus(302)
        ->assertSessionHasErrors(['slug']);
});

test('storing duplicate slug behaves normally if past collisions hold soft delete signatures', function () {
    Brand::create([
        'id' => '018cbf91-9b71-7000-8000-000000000600',
        'name' => 'Old Brand',
        'slug' => 'old-brand',
        'provider_id' => $this->defaultProvider->id,
        'category_id' => $this->defaultCategory->id,
        'is_active' => true,
        'is_featured' => false,
        'deleted_epoch' => 1719360000,
        'deleted_at' => '2026-06-25 22:00:00',
    ]);

    $payload = [
        'name' => 'Revived Old Brand',
        'slug' => 'old-brand',
        'provider_id' => $this->defaultProvider->id,
        'category_id' => $this->defaultCategory->id,
        'is_active' => true,
        'is_featured' => false,
    ];

    $this->actingAs($this->user, 'super_admin')
        ->post('/adm/catalog/brands', $payload)
        ->assertRedirect();

    $this->assertDatabaseHas('brands', [
        'name' => 'Revived Old Brand',
        'slug' => 'old-brand',
        'deleted_epoch' => 0,
    ]);
});

test('binary asset streaming updates physical storage references matching brand schemas', function () {
    $payload = [
        'name' => 'Multimedia Brand',
        'provider_id' => $this->defaultProvider->id,
        'category_id' => $this->defaultCategory->id,
        'image' => UploadedFile::fake()->image('brand_logo.webp'),
        'is_active' => true,
        'is_featured' => false,
    ];

    $this->actingAs($this->user, 'super_admin')
        ->post('/adm/catalog/brands', $payload)
        ->assertRedirect();

    $record = Brand::where('slug', 'multimedia-brand')->first();
    expect($record->image_path)->not->toBeNull();
});

test('brand incoming data pool structural verification matrix requests', function (Closure $data, string $field) {
    $this->actingAs($this->user, 'super_admin')
        ->post('/adm/catalog/brands', $data($this->defaultProvider->id, $this->defaultCategory->id))
        ->assertStatus(302)
        ->assertSessionHasErrors([$field]);
})->with('invalid_brand_payloads');

test('edit interface paths return valid view variables context elements hydration', function () {
    $brand = Brand::create([
        'id' => '018cbf91-9b71-7000-8000-000000000700',
        'name' => 'Fanta',
        'slug' => 'fanta',
        'provider_id' => $this->defaultProvider->id,
        'category_id' => $this->defaultCategory->id,
        'is_active' => true,
        'is_featured' => false,
    ]);

    $this->actingAs($this->user, 'super_admin')
        ->get("/adm/catalog/brands/{$brand->id}/edit")
        ->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Catalog/Brands/Edit')
            ->has('brand')
        );
});

test('update requests fail validation when pointing parent destination at itself', function () {
    $brand = Brand::create([
        'id' => '018cbf91-9b71-7000-8000-000000000800',
        'name' => 'Sprite',
        'slug' => 'sprite',
        'provider_id' => $this->defaultProvider->id,
        'category_id' => $this->defaultCategory->id,
        'is_active' => true,
        'is_featured' => false,
    ]);

    $payload = [
        'name' => 'Sprite Updated',
        'slug' => 'sprite',
        'provider_id' => $this->defaultProvider->id,
        'category_id' => $this->defaultCategory->id,
        'parent_id' => $brand->id,
        'is_active' => true,
        'is_featured' => false,
    ];

    $this->actingAs($this->user, 'super_admin')
        ->put("/adm/catalog/brands/{$brand->id}", $payload)
        ->assertStatus(302)
        ->assertSessionHasErrors(['parent_id']);
});

test('brands managing active tree levels refuse degradation changes if child elements remain live', function () {
    $parentBrand = Brand::create([
        'id' => '018cbf91-9b71-7000-8000-000000000900',
        'name' => 'Parent Group',
        'slug' => 'parent-group',
        'provider_id' => $this->defaultProvider->id,
        'category_id' => $this->defaultCategory->id,
        'is_active' => true,
        'is_featured' => false,
    ]);

    Brand::create([
        'id' => '018cbf91-9b71-7000-8000-000000000901',
        'name' => 'Child Branch',
        'slug' => 'child-branch',
        'parent_id' => $parentBrand->id,
        'provider_id' => $this->defaultProvider->id,
        'category_id' => $this->defaultCategory->id,
        'is_active' => true,
        'is_featured' => false,
    ]);

    $anotherRoot = Brand::create([
        'id' => '018cbf91-9b71-7000-8000-000000000902',
        'name' => 'External Group',
        'slug' => 'external-group',
        'provider_id' => $this->defaultProvider->id,
        'category_id' => $this->defaultCategory->id,
        'is_active' => true,
        'is_featured' => false,
    ]);

    $payload = [
        'name' => 'Parent Group Modified',
        'slug' => 'parent-group',
        'provider_id' => $this->defaultProvider->id,
        'category_id' => $this->defaultCategory->id,
        'parent_id' => $anotherRoot->id,
        'is_active' => true,
        'is_featured' => false,
    ];

    $this->actingAs($this->user, 'super_admin')
        ->put("/adm/catalog/brands/{$parentBrand->id}", $payload)
        ->assertStatus(302)
        ->assertSessionHasErrors(['parent_id']);
});

test('unlinked isolated brand units process standard soft deletion transformations', function () {
    $brand = Brand::create([
        'id' => '018cbf91-9b71-7000-8000-000000001000',
        'name' => 'Brand Without Items',
        'slug' => 'brand-without-items',
        'provider_id' => $this->defaultProvider->id,
        'category_id' => $this->defaultCategory->id,
        'is_active' => true,
        'is_featured' => false,
        'deleted_epoch' => 0,
    ]);

    $this->actingAs($this->user, 'super_admin')
        ->delete("/adm/catalog/brands/{$brand->id}")
        ->assertRedirect();

    $this->assertSoftDeleted('brands', [
        'id' => $brand->id,
    ]);

    $record = Brand::withTrashed()->find($brand->id);
    expect($record->deleted_epoch)->toBeGreaterThan(0);
});

test('active sub brand branches intercept parent destructions aborting removal flows', function () {
    $rootBrand = Brand::create([
        'id' => '018cbf91-9b71-7000-8000-000000001100',
        'name' => 'Main Head',
        'slug' => 'main-head',
        'provider_id' => $this->defaultProvider->id,
        'category_id' => $this->defaultCategory->id,
        'is_active' => true,
        'is_featured' => false,
    ]);

    Brand::create([
        'id' => '018cbf91-9b71-7000-8000-000000001101',
        'name' => 'Dependent Sub',
        'slug' => 'dependent-sub',
        'parent_id' => $rootBrand->id,
        'provider_id' => $this->defaultProvider->id,
        'category_id' => $this->defaultCategory->id,
        'is_active' => true,
        'is_featured' => false,
    ]);

    $this->actingAs($this->user, 'super_admin')
        ->delete("/adm/catalog/brands/{$rootBrand->id}")
        ->assertStatus(302)
        ->assertSessionHasErrors(['brand']);

    $this->assertDatabaseHas('brands', [
        'id' => $rootBrand->id,
        'deleted_at' => null,
    ]);
});

test('product entity catalog connections enforce referential lock structures over deletions', function () {
    $brand = Brand::create([
        'id' => '018cbf91-9b71-7000-8000-000000001200',
        'name' => 'Intel',
        'slug' => 'intel',
        'provider_id' => $this->defaultProvider->id,
        'category_id' => $this->defaultCategory->id,
        'is_active' => true,
        'is_featured' => false,
    ]);

    Product::create([
        'id' => '018cbf91-9b71-7000-8000-000000001201',
        'brand_id' => $brand->id,
        'category_id' => $this->defaultCategory->id,
        'name' => 'Core i9 Processor',
        'slug' => 'core-i9-processor',
        'is_active' => true,
        'is_featured' => false,
    ]);

    $this->actingAs($this->user, 'super_admin')
        ->delete("/adm/catalog/brands/{$brand->id}")
        ->assertStatus(302)
        ->assertSessionHasErrors(['brand']);

    $this->assertDatabaseHas('brands', [
        'id' => $brand->id,
        'deleted_at' => null,
    ]);
});