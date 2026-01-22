<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Sku;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // 1. CERVEZAS
        $this->createFullProduct('Paceña Pilsener', 'Paceña', 'cervezas', [
            ['name' => 'Botella 620ml', 'price' => 13.00, 'weight' => 1.1, 'code' => '777001001'],
            ['name' => 'Lata 354ml', 'price' => 8.00, 'weight' => 0.37, 'code' => '777001002'],
            ['name' => 'Six Pack Latas', 'price' => 45.00, 'weight' => 2.2, 'code' => '777001003', 'factor' => 6],
            ['name' => 'Caja 12 Botellas', 'price' => 150.00, 'weight' => 13.2, 'code' => '777001004', 'factor' => 12],
        ]);

        $this->createFullProduct('Huari Tradicional', 'Huari', 'cervezas', [
            ['name' => 'Botella 620ml', 'price' => 16.00, 'weight' => 1.1, 'code' => '777002001'],
            ['name' => 'Botella 330ml', 'price' => 10.00, 'weight' => 0.6, 'code' => '777002002'],
        ]);

        $this->createFullProduct('Corona Extra', 'Corona', 'cervezas', [
            ['name' => 'Botella 355ml', 'price' => 14.00, 'weight' => 0.6, 'code' => '750001001'],
            ['name' => 'Six Pack 355ml', 'price' => 80.00, 'weight' => 3.6, 'code' => '750001006', 'factor' => 6],
        ]);

        // 2. GASEOSAS
        $this->createFullProduct('Coca-Cola Original', 'Coca-Cola', 'gaseosas', [
            ['name' => 'Botella 2L', 'price' => 11.00, 'weight' => 2.1, 'code' => '777003001'],
            ['name' => 'Botella 3L', 'price' => 14.00, 'weight' => 3.1, 'code' => '777003002'],
            ['name' => 'Pack 6x2L', 'price' => 62.00, 'weight' => 12.6, 'code' => '777003003', 'factor' => 6],
            ['name' => 'Mini 300ml', 'price' => 3.00, 'weight' => 0.35, 'code' => '777003004'],
        ]);

        // 3. DESTILADOS
        $this->createFullProduct('Singani Casa Real Etiqueta Negra', 'Casa Real', 'singani', [
            ['name' => 'Botella 750ml', 'price' => 85.00, 'weight' => 1.2, 'code' => '777004001'],
        ]);

        $this->createFullProduct('Fernet Branca', 'Fernet Branca', 'fernet', [
            ['name' => 'Botella 750ml', 'price' => 90.00, 'weight' => 1.4, 'code' => '779001001'],
            ['name' => 'Botella 450ml', 'price' => 60.00, 'weight' => 0.8, 'code' => '779001002'],
        ]);
        
        $this->createFullProduct('Whisky Johnnie Walker Black Label', 'Johnnie Walker', 'whisky', [
            ['name' => 'Botella 750ml', 'price' => 280.00, 'weight' => 1.4, 'code' => '500001001'],
            ['name' => 'Botella 1L', 'price' => 350.00, 'weight' => 1.8, 'code' => '500001002'],
        ]);
    }

    private function createFullProduct($productName, $brandName, $catSlug, $variants)
    {
        $brand = Brand::where('name', $brandName)->first();
        // Búsqueda flexible de categorías
        $category = Category::where('slug', 'like', "%$catSlug%")->first();

        if (!$brand || !$category) {
            // dump("Saltando $productName: Marca o Categ no encontrada.");
            return;
        }

        // Crear Producto Padre
        $product = Product::firstOrCreate([
            'name' => $productName
        ], [
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'slug' => Str::slug($productName),
            'description' => "La mejor calidad de $brandName en su variedad $productName.",
            'is_active' => true,
            'is_alcoholic' => $catSlug !== 'gaseosas'
        ]);

        // Crear SKUs y asignar Precios
        foreach ($variants as $v) {
            /** @var Sku $sku */
            $sku = Sku::firstOrCreate([
                'product_id' => $product->id,
                'name' => $v['name']
            ], [
                'code' => $v['code'] ?? null,
                'conversion_factor' => $v['factor'] ?? 1,
                'weight' => $v['weight'] ?? 1.0,
                'is_active' => true
            ]);

            // CORRECCIÓN CENTRAL:
            // Usamos el helper del modelo Sku.
            // Esto asegura consistencia con el Controller y la lógica de SoftDeletes.
            // Al no pasar branch_id, se crea como Precio Base Nacional (null).
            $sku->updatePrice((float) $v['price']);
        }
    }
}