<?php

use App\Models\Users\Admin;
use App\Models\Catalog\Category;
use Spatie\Permission\Models\Role;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'super_admin']);
    
    Storage::fake('public');
});


test('un super admin puede crear una categoria raiz, generando el slug, resolviendo el sort_order secuencial y almacenando multimedia', function () {
    $superAdmin = Admin::factory()->create(['is_active' => true]);
    $superAdmin->assignRole('super_admin');

    DB::table('categories')->insert([
        'id' => Str::uuid()->toString(),
        'name' => 'Categoría Inicial',
        'slug' => 'categoria-inicial',
        'requires_age_check' => false,
        'is_active' => true,
        'is_featured' => false,
        'sort_order' => 5, 
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $image = UploadedFile::fake()->image('banner.jpg', 800, 600)->size(1500);
    $icon = UploadedFile::fake()->image('icon.png', 100, 100)->size(200);

    $payload = [
        'name' => 'Lácteos y Quesos',
        'slug' => null, 
        'parent_id' => null,
        'external_code' => 'CAT-LAC-2026',
        'tax_classification' => 'IVA_13',
        'requires_age_check' => false,
        'is_active' => true,
        'is_featured' => true,
        'bg_color' => '#FFAA00',
        'image' => $image,
        'icon' => $icon,
        'description' => 'Todos los productos derivados de la leche.',
        'seo_title' => 'Comprar Lácteos Online',
        'seo_description' => 'Encuentra los mejores lácteos frescos aquí.',
    ];

    $response = $this->actingAs($superAdmin, 'super_admin')
        ->from(route('admin.catalog.categories.create'))
        ->post(route('admin.catalog.categories.store'), $payload);

    $response->assertStatus(302);
    $response->assertRedirect(route('admin.catalog.categories.index'));

    $this->assertDatabaseHas('categories', [
        'name' => 'Lácteos y Quesos',
        'slug' => 'lacteos-y-quesos',
        'parent_id' => null,
        'external_code' => 'CAT-LAC-2026',
        'sort_order' => 6, 
        'is_featured' => true,
        'bg_color' => '#FFAA00',
        'deleted_epoch' => 0,
    ]);

    $category = Category::where('external_code', 'CAT-LAC-2026')->first();
    expect($category->image_path)->not->toBeNull()
        ->and($category->icon_path)->not->toBeNull();

    Storage::disk('public')->assertExists($category->image_path);
    Storage::disk('public')->assertExists($category->icon_path);
});

test('un super admin puede crear una subcategoria (Nivel 2) vinculada correctamente a un padre valido', function () {
    $superAdmin = Admin::factory()->create(['is_active' => true]);
    $superAdmin->assignRole('super_admin');

    // Crear la categoría raíz (Nivel 1)
    $parentId = Str::uuid()->toString();
    DB::table('categories')->insert([
        'id' => $parentId,
        'name' => 'Bebidas',
        'slug' => 'bebidas',
        'parent_id' => null,
        'requires_age_check' => false,
        'is_active' => true,
        'is_featured' => false,
        'sort_order' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $payload = [
        'name' => 'Bebidas Energizantes',
        'slug' => 'energizantes-premium', 
        'parent_id' => $parentId,
        'external_code' => 'SUB-ENER-01',
        'requires_age_check' => true,
        'is_active' => true,
        'is_featured' => false,
    ];

    $response = $this->actingAs($superAdmin, 'super_admin')
        ->post(route('admin.catalog.categories.store'), $payload);

    $response->assertStatus(302);

    $this->assertDatabaseHas('categories', [
        'name' => 'Bebidas Energizantes',
        'slug' => 'energizantes-premium',
        'parent_id' => $parentId,
        'requires_age_check' => true,
    ]);
});

test('un super admin puede modificar una categoria ignorando sus propias reglas de unicidad sobre registros vivos', function () {
    $superAdmin = Admin::factory()->create(['is_active' => true]);
    $superAdmin->assignRole('super_admin');

    $catId = Str::uuid()->toString();
    DB::table('categories')->insert([
        'id' => $catId,
        'name' => 'Limpieza original',
        'slug' => 'limpieza-original',
        'external_code' => 'CODE-LIMP',
        'requires_age_check' => false,
        'is_active' => true,
        'is_featured' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $updatePayload = [
        'name' => 'Limpieza Modificada',
        'slug' => 'limpieza-original', 
        'external_code' => 'CODE-LIMP', 
        'requires_age_check' => false,
        'is_active' => false,
        'is_featured' => true,
    ];

    $response = $this->actingAs($superAdmin, 'super_admin')
        ->from(route('admin.catalog.categories.edit', ['category' => $catId]))
        ->put(route('admin.catalog.categories.update', ['category' => $catId]), $updatePayload);

    $response->assertStatus(302);
    $response->assertRedirect(route('admin.catalog.categories.index'));

    $this->assertDatabaseHas('categories', [
        'id' => $catId,
        'name' => 'Limpieza Modificada',
        'is_active' => false,
    ]);
});


test('el sistema rechaza de forma categorica la creacion de un tercer nivel en la jerarquia', function () {
    $superAdmin = Admin::factory()->create(['is_active' => true]);
    $superAdmin->assignRole('super_admin');

    $rootId = Str::uuid()->toString();
    $subCategoryId = Str::uuid()->toString();

    DB::table('categories')->insert([
        'id' => $rootId, 'name' => 'Raíz', 'slug' => 'raiz', 'parent_id' => null, 'is_active' => true, 'is_featured' => false, 'created_at' => now()
    ]);
    DB::table('categories')->insert([
        'id' => $subCategoryId, 'name' => 'Hijo', 'slug' => 'hijo', 'parent_id' => $rootId, 'is_active' => true, 'is_featured' => false, 'created_at' => now()
    ]);

    $payload = [
        'name' => 'Nieto Prohibido',
        'slug' => 'nieto-prohibido',
        'parent_id' => $subCategoryId,
        'requires_age_check' => false,
        'is_active' => true,
        'is_featured' => false,
    ];

    $response = $this->actingAs($superAdmin, 'super_admin')
        ->from(route('admin.catalog.categories.create'))
        ->post(route('admin.catalog.categories.store'), $payload);

    $response->assertStatus(302);
    $response->assertSessionHasErrors(['parent_id']);
    $this->assertDatabaseMissing('categories', ['name' => 'Nieto Prohibido']);
});

test('una categoria no puede ser configurada como hija de si misma durante la actualizacion', function () {
    $superAdmin = Admin::factory()->create(['is_active' => true]);
    $superAdmin->assignRole('super_admin');

    $category = Category::create([
        'id' => Str::uuid()->toString(),
        'name' => 'Auto Dependiente',
        'slug' => 'auto-dependiente',
        'requires_age_check' => false,
        'is_active' => true,
        'is_featured' => false,
    ]);

    $payload = [
        'name' => 'Auto Dependiente Mod',
        'slug' => 'auto-dependiente',
        'parent_id' => $category->id,
        'requires_age_check' => false,
        'is_active' => true,
        'is_featured' => false,
    ];

    $response = $this->actingAs($superAdmin, 'super_admin')
        ->from(route('admin.catalog.categories.edit', ['category' => $category->id]))
        ->put(route('admin.catalog.categories.update', ['category' => $category->id]), $payload);

    $response->assertStatus(302);
    $response->assertSessionHasErrors([
        'parent_id' => 'Operación inválida: Una categoría no puede ser hija de sí misma.'
    ]);
});

test('una categoria con subcategorias asignadas no puede descender en la jerarquia', function () {
    $superAdmin = Admin::factory()->create(['is_active' => true]);
    $superAdmin->assignRole('super_admin');

    $rootA = Category::create(['id' => Str::uuid()->toString(), 'name' => 'Raíz A', 'slug' => 'raiz-a', 'requires_age_check' => false, 'is_active' => true, 'is_featured' => false]);
    $rootB = Category::create(['id' => Str::uuid()->toString(), 'name' => 'Raíz B', 'slug' => 'raiz-b', 'requires_age_check' => false, 'is_active' => true, 'is_featured' => false]);
    
    Category::create(['id' => Str::uuid()->toString(), 'name' => 'Sub Hija', 'slug' => 'sub-hija', 'parent_id' => $rootA->id, 'requires_age_check' => false, 'is_active' => true, 'is_featured' => false]);

    $payload = [
        'name' => 'Raíz A Modificada',
        'slug' => 'raiz-a',
        'parent_id' => $rootB->id, 
        'requires_age_check' => false,
        'is_active' => true,
        'is_featured' => false,
    ];

    $response = $this->actingAs($superAdmin, 'super_admin')
        ->from(route('admin.catalog.categories.edit', ['category' => $rootA->id]))
        ->put(route('admin.catalog.categories.update', ['category' => $rootA->id]), $payload);

    $response->assertStatus(302);
    $response->assertSessionHasErrors([
        'parent_id' => 'Restricción de nivel: Esta categoría ya posee subcategorías asignadas y no puede descender en la jerarquía.'
    ]);
});



dataset('payloads_categorias_erroneas', [
    'nombre ausente' => [['name' => ''], 'name'],
    'formato color hex invalido' => [['bg_color' => '#GHIJK12'], 'bg_color'],
    'imagen excesiva' => [['image' => 'not-a-file-object'], 'image'],
    'datos booleanos corruptos' => [['requires_age_check' => 'string-values'], 'requires_age_check'],
]);

test('el contrato tecnico del catalogo valida de forma estricta tipados, regex de colores y nulidades', function (array $invalidData, string $expectedErrorKey) {
    $superAdmin = Admin::factory()->create(['is_active' => true]);
    $superAdmin->assignRole('super_admin');

    $payload = array_merge([
        'name' => 'Categoría Control',
        'requires_age_check' => false,
        'is_active' => true,
        'is_featured' => false,
    ], $invalidData);

    $response = $this->actingAs($superAdmin, 'super_admin')
        ->from(route('admin.catalog.categories.create'))
        ->post(route('admin.catalog.categories.store'), $payload);

    $response->assertStatus(302);
    $response->assertSessionHasErrors($expectedErrorKey);
})->with('payloads_categorias_erroneas');



test('un usuario sin el rol administrativo super_admin no puede acceder a las mutaciones del catalogo', function () {
    $unauthorizedAdmin = Admin::factory()->create(['is_active' => true]);

    $payload = [
        'name' => 'Categoría Intrusión',
        'requires_age_check' => false,
        'is_active' => true,
        'is_featured' => false,
    ];

    $response = $this->actingAs($unauthorizedAdmin, 'super_admin')
        ->post(route('admin.catalog.categories.store'), $payload);

    $response->assertStatus(403);
    $this->assertDatabaseMissing('categories', ['name' => 'Categoría Intrusión']);
});

test('el sistema mitiga de manera nativa los vectores de ataque distribuidos en campos de texto del catalogo', function () {
    $superAdmin = Admin::factory()->create(['is_active' => true]);
    $superAdmin->assignRole('super_admin');

    $payload = [
        'name' => "Cat' UNION SELECT * FROM users --",
        'slug' => "xss-payload-test",
        'description' => "<div onclick='alert(1)'>Ataque XSS inyectado</div>",
        'requires_age_check' => false,
        'is_active' => true,
        'is_featured' => false,
    ];

    $response = $this->actingAs($superAdmin, 'super_admin')
        ->post(route('admin.catalog.categories.store'), $payload);

    $response->assertStatus(302);
    $this->assertDatabaseHas('categories', [
        'name' => "Cat' UNION SELECT * FROM users --",
        'description' => "<div onclick='alert(1)'>Ataque XSS inyectado</div>",
    ]);
});