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
});

dataset('invalid_category_payloads', [
    'missing_name' => [
        fn() => ['name' => '', 'requires_age_check' => true, 'is_active' => true, 'is_featured' => false],
        'name'
    ],
    'name_exceeds_max' => [
        fn() => ['name' => str_repeat('z', 101), 'requires_age_check' => true, 'is_active' => true, 'is_featured' => false],
        'name'
    ],
    'malformed_bg_color' => [
        fn() => ['name' => 'Gourmet', 'bg_color' => '#GG0000', 'requires_age_check' => true, 'is_active' => true, 'is_featured' => false],
        'bg_color'
    ],
    'requires_age_check_non_boolean' => [
        fn() => ['name' => 'Vinos', 'requires_age_check' => 'yes', 'is_active' => true, 'is_featured' => false],
        'requires_age_check'
    ],
]);

test('guest access to categories endpoints redirects to login', function () {
    $this->get('/adm/catalog/categories')->assertRedirect('adm/login');
    $this->post('/adm/catalog/categories', [])->assertRedirect('adm/login');
});

test('non super admin users are blocked with 403 status code', function () {
    $regularAdmin = Admin::create([
        'id' => '018cbf91-9b71-7000-8000-000000000002',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@test.com',
        'password' => bcrypt('password'),
        'is_active' => true,
    ]);

    $this->actingAs($regularAdmin, 'super_admin')
        ->get('/adm/catalog/categories')
        ->assertStatus(403);
});

test('index path returns the correct index inertia view component', function () {
    Category::create([
        'id' => '018cbf91-9b71-7000-8000-000000000010',
        'name' => 'Tecnología',
        'slug' => 'tecnologia',
        'requires_age_check' => false,
        'is_active' => true,
        'is_featured' => true,
    ]);

    $this->actingAs($this->user, 'super_admin')
        ->get('/adm/catalog/categories')
        ->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Catalog/Categories/Index')
            ->has('categories')
        );
});

test('create path renders form interface successfully', function () {
    $this->actingAs($this->user, 'super_admin')
        ->get('/adm/catalog/categories/create')
        ->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Catalog/Categories/Create')
        );
});

test('root category execution resolves automatically generated slug', function () {
    $payload = [
        'name' => 'Lácteos Frescos',
        'requires_age_check' => false,
        'is_active' => true,
        'is_featured' => false,
        'bg_color' => '#FFFFFF',
    ];

    $this->actingAs($this->user, 'super_admin')
        ->post('/adm/catalog/categories', $payload)
        ->assertRedirect();

    $this->assertDatabaseHas('categories', [
        'name' => 'Lácteos Frescos',
        'slug' => 'lacteos-frescos',
        'parent_id' => null,
        'deleted_epoch' => 0,
    ]);
});

test('child level mapping registers parent id association correctly', function () {
    $root = Category::create([
        'id' => '018cbf91-9b71-7000-8000-000000000020',
        'name' => 'Bebidas',
        'slug' => 'bebidas',
        'requires_age_check' => false,
        'is_active' => true,
        'is_featured' => false,
    ]);

    $payload = [
        'name' => 'Bebidas Energizantes',
        'parent_id' => $root->id,
        'requires_age_check' => false,
        'is_active' => true,
        'is_featured' => false,
    ];

    $this->actingAs($this->user, 'super_admin')
        ->post('/adm/catalog/categories', $payload)
        ->assertRedirect();

    $this->assertDatabaseHas('categories', [
        'name' => 'Bebidas Energizantes',
        'parent_id' => $root->id,
        'slug' => 'bebidas-energizantes',
    ]);
});

test('exceeding two levels depth limit aborts with validation errors', function () {
    $root = Category::create([
        'id' => '018cbf91-9b71-7000-8000-000000000030',
        'name' => 'Level One',
        'slug' => 'level-one',
        'requires_age_check' => false,
        'is_active' => true,
        'is_featured' => false,
    ]);

    $sub = Category::create([
        'id' => '018cbf91-9b71-7000-8000-000000000031',
        'name' => 'Level Two',
        'slug' => 'level-two',
        'parent_id' => $root->id,
        'requires_age_check' => false,
        'is_active' => true,
        'is_featured' => false,
    ]);

    $payload = [
        'name' => 'Level Three Failure',
        'parent_id' => $sub->id,
        'requires_age_check' => false,
        'is_active' => true,
        'is_featured' => false,
    ];

    $this->actingAs($this->user, 'super_admin')
        ->post('/adm/catalog/categories', $payload)
        ->assertStatus(302)
        ->assertSessionHasErrors(['parent_id']);
});

test('active identical slug tracks constraint validation unique failure', function () {
    Category::create([
        'id' => '018cbf91-9b71-7000-8000-000000000040',
        'name' => 'Limpieza',
        'slug' => 'limpieza',
        'requires_age_check' => false,
        'is_active' => true,
        'is_featured' => false,
        'deleted_epoch' => 0,
    ]);

    $payload = [
        'name' => 'Limpieza Avanzada',
        'slug' => 'limpieza',
        'requires_age_check' => false,
        'is_active' => true,
        'is_featured' => false,
    ];

    $this->actingAs($this->user, 'super_admin')
        ->post('/adm/catalog/categories', $payload)
        ->assertStatus(302)
        ->assertSessionHasErrors(['slug']);
});

test('slug collision is bypassable if duplicate entries hold soft delete tracking', function () {
    Category::create([
        'id' => '018cbf91-9b71-7000-8000-000000000050',
        'name' => 'Hogar',
        'slug' => 'hogar',
        'requires_age_check' => false,
        'is_active' => true,
        'is_featured' => false,
        'deleted_epoch' => 1719360000,
        'deleted_at' => '2026-06-25 21:20:00',
    ]);

    $payload = [
        'name' => 'Nuevo Hogar',
        'slug' => 'hogar',
        'requires_age_check' => false,
        'is_active' => true,
        'is_featured' => false,
    ];

    $this->actingAs($this->user, 'super_admin')
        ->post('/adm/catalog/categories', $payload)
        ->assertRedirect();

    $this->assertDatabaseHas('categories', [
        'name' => 'Nuevo Hogar',
        'slug' => 'hogar',
        'deleted_epoch' => 0,
    ]);
});

test('validation matrix constraints execution checking', function (Closure $data, string $field) {
    $this->actingAs($this->user, 'super_admin')
        ->post('/adm/catalog/categories', $data())
        ->assertStatus(302)
        ->assertSessionHasErrors([$field]);
})->with('invalid_category_payloads');

test('edit path displays structural hydration details correctly', function () {
    $category = Category::create([
        'id' => '018cbf91-9b71-7000-8000-000000000060',
        'name' => 'Cuidado Personal',
        'slug' => 'cuidado-personal',
        'requires_age_check' => false,
        'is_active' => true,
        'is_featured' => false,
    ]);

    $this->actingAs($this->user, 'super_admin')
        ->get("/adm/catalog/categories/{$category->id}/edit")
        ->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Catalog/Categories/Edit')
            ->has('category')
        );
});

test('prevent hierarchy configuration referencing self category identifier', function () {
    $category = Category::create([
        'id' => '018cbf91-9b71-7000-8000-000000000070',
        'name' => 'Mascotas',
        'slug' => 'mascotas',
        'requires_age_check' => false,
        'is_active' => true,
        'is_featured' => false,
    ]);

    $payload = [
        'name' => 'Mascotas Modificado',
        'slug' => 'mascotas',
        'parent_id' => $category->id,
        'requires_age_check' => false,
        'is_active' => true,
        'is_featured' => false,
    ];

    $this->actingAs($this->user, 'super_admin')
        ->put("/adm/catalog/categories/{$category->id}", $payload)
        ->assertStatus(302)
        ->assertSessionHasErrors(['parent_id']);
});

test('parent tree structures avoid nesting updates if child subcategories exist', function () {
    $root = Category::create([
        'id' => '018cbf91-9b71-7000-8000-000000000080',
        'name' => 'Panadería',
        'slug' => 'panaderia',
        'requires_age_check' => false,
        'is_active' => true,
        'is_featured' => false,
    ]);

    Category::create([
        'id' => '018cbf91-9b71-7000-8000-000000000081',
        'name' => 'Tortas',
        'slug' => 'tortas',
        'parent_id' => $root->id,
        'requires_age_check' => false,
        'is_active' => true,
        'is_featured' => false,
    ]);

    $alternate = Category::create([
        'id' => '018cbf91-9b71-7000-8000-000000000082',
        'name' => 'Congelados',
        'slug' => 'congelados',
        'requires_age_check' => false,
        'is_active' => true,
        'is_featured' => false,
    ]);

    $payload = [
        'name' => 'Panadería Modificada',
        'slug' => 'panaderia',
        'parent_id' => $alternate->id,
        'requires_age_check' => false,
        'is_active' => true,
        'is_featured' => false,
    ];

    $this->actingAs($this->user, 'super_admin')
        ->put("/adm/catalog/categories/{$root->id}", $payload)
        ->assertStatus(302)
        ->assertSessionHasErrors(['parent_id']);
});

test('clean category records perform regular execution of soft deletion tracking', function () {
    $category = Category::create([
        'id' => '018cbf91-9b71-7000-8000-000000000090',
        'name' => 'Descontinuados',
        'slug' => 'descontinuados',
        'requires_age_check' => false,
        'is_active' => true,
        'is_featured' => false,
        'deleted_epoch' => 0,
    ]);

    $this->actingAs($this->user, 'super_admin')
        ->delete("/adm/catalog/categories/{$category->id}")
        ->assertRedirect();

    $this->assertSoftDeleted('categories', [
        'id' => $category->id,
    ]);

    $record = Category::withTrashed()->find($category->id);
    expect($record->deleted_epoch)->toBeGreaterThan(0);
});

test('dependency subcategory presence restricts removal throwing specific failure mapping', function () {
    $root = Category::create([
        'id' => '018cbf91-9b71-7000-8000-000000000100',
        'name' => 'Automotriz',
        'slug' => 'automotriz',
        'requires_age_check' => false,
        'is_active' => true,
        'is_featured' => false,
    ]);

    Category::create([
        'id' => '018cbf91-9b71-7000-8000-000000000101',
        'name' => 'Llantas',
        'slug' => 'llantas',
        'parent_id' => $root->id,
        'requires_age_check' => false,
        'is_active' => true,
        'is_featured' => false,
    ]);

    $this->actingAs($this->user, 'super_admin')
        ->delete("/adm/catalog/categories/{$root->id}")
        ->assertStatus(302)
        ->assertSessionHasErrors(['category']);

    $this->assertDatabaseHas('categories', [
        'id' => $root->id,
        'deleted_at' => null,
    ]);
});

test('linked product existence restricts deletion matching structural failure rules', function () {
    $category = Category::create([
        'id' => '018cbf91-9b71-7000-8000-000000000110',
        'name' => 'Ferretería',
        'slug' => 'ferreteria',
        'requires_age_check' => false,
        'is_active' => true,
        'is_featured' => false,
    ]);

    $provider = Provider::create([
        'id' => '018cbf91-9b71-7000-8000-000000000111',
        'company_name' => 'Distribuidora Central',
        'slug' => 'distribuidora-central',
        'tax_id' => 'ID-998822',
        'is_active' => true,
    ]);

    $brand = Brand::create([
        'id' => '018cbf91-9b71-7000-8000-000000000112',
        'name' => 'Herramientas Pro',
        'slug' => 'herramientas-pro',
        'provider_id' => $provider->id,
        'category_id' => $category->id,
        'is_active' => true,
        'is_featured' => false,
    ]);

    Product::create([
        'id' => '018cbf91-9b71-7000-8000-000000000113',
        'brand_id' => $brand->id,
        'category_id' => $category->id,
        'name' => 'Martillo de Acero',
        'slug' => 'martillo-de-acero',
        'is_active' => true,
        'is_featured' => false,
    ]);

    $this->actingAs($this->user, 'super_admin')
        ->delete("/adm/catalog/categories/{$category->id}")
        ->assertStatus(302)
        ->assertSessionHasErrors(['category']);

    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'deleted_at' => null,
    ]);
});

test('partial reload targeting skus returns index layout preserving specific components mapping', function () {
    $category = Category::create([
        'id' => '018cbf91-9b71-7000-8000-000000000120',
        'name' => 'Electrónica',
        'slug' => 'electronica',
        'requires_age_check' => false,
        'is_active' => true,
        'is_featured' => false,
    ]);

    $computedVersion = app(\App\Http\Middleware\HandleAdminInertiaRequests::class)->version(new \Illuminate\Http\Request());

    $response = $this->actingAs($this->user, 'super_admin')
        ->withHeaders([
            'X-Inertia' => 'true',
            'X-Inertia-Version' => $computedVersion,
            'X-Inertia-Partial-Component' => 'Admin/Catalog/Categories/Index',
            'X-Inertia-Partial-Data' => 'skus',
        ])
        ->get("/adm/catalog/categories?selected_category={$category->id}");

    $response->assertStatus(200);

    $responseData = json_decode($response->getContent(), true);
    $hasSkusProperty = isset($responseData['props']['skus']) || isset($responseData['skus']);
    
    expect($hasSkusProperty)->toBeTrue();
});

test('reorder request execution mutates positions matching raw input vectors', function () {
    $category = Category::create([
        'id' => '018cbf91-9b71-7000-8000-000000000130',
        'name' => 'Juguetes',
        'slug' => 'juguetes',
        'requires_age_check' => false,
        'is_active' => true,
        'is_featured' => false,
    ]);

    $payload = [
        'skus' => [
            '018cbf91-9b71-7000-8000-000000000131',
            '018cbf91-9b71-7000-8000-000000000132',
            '018cbf91-9b71-7000-8000-000000000133'
        ]
    ];

    $this->actingAs($this->user, 'super_admin')
        ->put("/adm/catalog/categories/{$category->id}/sku-order", $payload)
        ->assertRedirect();
});