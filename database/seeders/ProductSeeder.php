<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Sku;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        // 1. PRODUCTOS REALES (Mantenemos tu lógica original para demos exactas)
        $this->createFullProduct('Paceña Pilsener', 'Paceña', 'cervezas', [
            ['name' => 'Botella 620ml', 'price' => 13.00, 'weight' => 1.1, 'code' => '777001001'],
            ['name' => 'Lata 354ml', 'price' => 8.00, 'weight' => 0.37, 'code' => '777001002'],
            ['name' => 'Six Pack Latas', 'price' => 45.00, 'weight' => 2.2, 'code' => '777001003', 'factor' => 6],
        ]);
        // ... (Mantenemos el resto de tus productos reales: Huari, CocaCola, etc.)

        // 2. PRODUCTOS GENERADOS (MASIVOS)
        $brands = Brand::all();
        $categories = Category::whereNotNull('parent_id')->get(); // Solo subcategorías

        if($brands->isEmpty() || $categories->isEmpty()) return;

        for ($i = 0; $i < 50; $i++) {
            $randomBrand = $brands->random();
            $randomCat = $categories->random();
            
            // Nombre Ej: "Vodka Absolut Raspberry" (Simulado)
            $productName = $randomBrand->name . ' ' . ucfirst($faker->word) . ' ' . $faker->randomElement(['Gold', 'Silver', 'Black', 'Reserve', 'Ice']);
            
            $variants = [];
            
            // Generar 1 a 3 variantes por producto
            $numVariants = $faker->numberBetween(1, 3);
            
            for ($j = 0; $j < $numVariants; $j++) {
                $type = $faker->randomElement(['Botella 750ml', 'Botella 1L', 'Lata 350ml', 'Pack 6u', 'Caja 12u']);
                $basePrice = $faker->randomFloat(2, 10, 300);
                
                $variants[] = [
                    'name' => $type,
                    'price' => $basePrice,
                    'weight' => $faker->randomFloat(2, 0.3, 2.0),
                    'code' => $faker->ean13,
                    'factor' => str_contains($type, 'Pack') ? 6 : (str_contains($type, 'Caja') ? 12 : 1)
                ];
            }

            $this->createFullProduct($productName, $randomBrand->name, $randomCat->slug, $variants);
        }
    }

    private function createFullProduct($productName, $brandName, $catSlug, $variants)
    {
        $brand = Brand::where('name', $brandName)->first();
        // Búsqueda flexible (por slug exacto o parcial para los generados)
        $category = Category::where('slug', 'like', "%$catSlug%")->orWhere('slug', $catSlug)->first();

        // Si es generado y no tiene categoria especifica, asignar una random si falla la busqueda
        if (!$category) $category = Category::inRandomOrder()->first();
        if (!$brand) return; // Si la marca no existe saltamos (aunque en el loop usamos marcas existentes)

        $product = Product::firstOrCreate([
            'name' => $productName
        ], [
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'slug' => Str::slug($productName) . '-' . Str::random(5), // Slug único
            'description' => "Descripción generada para $productName.",
            'is_active' => true,
            'is_alcoholic' => !str_contains($category->slug, 'gaseosa') && !str_contains($category->slug, 'agua')
        ]);

        foreach ($variants as $v) {
            $sku = Sku::firstOrCreate([
                'product_id' => $product->id,
                'name' => $v['name']
            ], [
                'code' => $v['code'] ?? null,
                'conversion_factor' => $v['factor'] ?? 1,
                'weight' => $v['weight'] ?? 1.0,
                'is_active' => true
            ]);

            $sku->updatePrice((float) $v['price']);
        }
    }
}