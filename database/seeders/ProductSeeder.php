<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Product, Sku, Brand, Category, Branch, Price};
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Cargamos dependencias necesarias para la matriz
        $subCategories = Category::whereNotNull('parent_id')->get();
        $branches = Branch::all(); // Necesario para inicializar la matriz de precios

        if ($subCategories->isEmpty()) {
            $this->command->error('No hay subcategorías. Ejecuta CategorySeeder primero.');
            return;
        }

        foreach ($subCategories as $category) {
            $productsData = $this->generateRealProducts($category->name, 3);

            foreach ($productsData as $prodData) {
                DB::transaction(function () use ($prodData, $category, $branches) {
                    
                    // 2. Gestión de Marca (Idempotente)
                    $brand = Brand::updateOrCreate(
                        ['slug' => Str::slug($prodData['brand'])],
                        ['name' => $prodData['brand'], 'is_active' => true]
                    );

                    // 3. Gestión de Producto (Llave: nombre + marca para evitar colisiones)
                    $product = Product::updateOrCreate(
                        [
                            'name' => $prodData['name'],
                            'brand_id' => $brand->id 
                        ],
                        [
                            'category_id' => $category->id,
                            'slug' => Str::slug($prodData['name']) . '-' . Str::random(4),
                            'description' => "Calidad premium garantizada de " . $prodData['brand'],
                            'is_active' => true,
                            'is_alcoholic' => $category->is_alcoholic ?? false // Ajustado a tu modelo real
                        ]
                    );

                    $this->createSkusAndPrices($product, $prodData['base_price'], $branches);
                });
            }
        }
    }

    private function createSkusAndPrices($product, $basePrice, $branches): void
    {
        $variations = [
            ['suffix' => 'Unitario', 'factor' => 1, 'price_mult' => 1.0, 'code_suf' => 'UNI'],
            ['suffix' => 'Pack x6', 'factor' => 6, 'price_mult' => 5.8, 'code_suf' => 'PK6'], 
            ['suffix' => 'Caja x12', 'factor' => 12, 'price_mult' => 11.2, 'code_suf' => 'C12'],
        ];

        foreach ($variations as $var) {
            $skuName = "{$product->name} ({$var['suffix']})";
            $calculatedPrice = $basePrice * $var['price_mult'];

            // Hash determinista para el código EAN si no existe
            $skuCode = "777" . strtoupper(substr(md5($product->id . $var['code_suf']), 0, 10));

            // 4. Creación Quirúrgica del SKU
            $sku = Sku::updateOrCreate(
                ['code' => $skuCode],
                [
                    'product_id' => $product->id,
                    'name' => $skuName,
                    'base_price' => $calculatedPrice,
                    'conversion_factor' => $var['factor'],
                    'weight' => rand(500, 2000) / 1000,
                    'is_active' => true
                ]
            );

            // 5. INICIALIZACIÓN DE MATRIZ DE PRECIOS (Crítico para tu nueva UI)
            foreach ($branches as $branch) {
                // Creamos un precio 'regular' para cada sucursal
                // Algunos con ligero sobreprecio aleatorio para testear la matriz
                $branchMarkup = rand(0, 5) / 100; 
                $finalPrice = $calculatedPrice * (1 + $branchMarkup);

                Price::updateOrCreate(
                    [
                        'sku_id' => $sku->id,
                        'branch_id' => $branch->id,
                        'type' => 'regular'
                    ],
                    [
                        'list_price' => $finalPrice * 1.15, // PVP sugerido 15% arriba
                        'final_price' => $finalPrice,
                        'min_quantity' => 1,
                        'priority' => 1,
                        'valid_from' => now(),
                    ]
                );

                // Sembrar una oferta aleatoria para ver el "Price Stack" en la UI
                if (rand(1, 10) > 8) {
                    Price::create([
                        'sku_id' => $sku->id,
                        'branch_id' => $branch->id,
                        'type' => 'offer',
                        'list_price' => $finalPrice,
                        'final_price' => $finalPrice * 0.8,
                        'min_quantity' => 1,
                        'priority' => 10, // Mayor prioridad para que gane en la UI
                        'valid_from' => now(),
                        'valid_to' => now()->addDays(7)
                    ]);
                }
            }
        }
    }

    private function generateRealProducts($categoryName, $qty): array
    {
        $context = $this->getContextByKeyword($categoryName);
        $products = [];

        for ($i = 0; $i < $qty; $i++) {
            $brand = $context['brands'][array_rand($context['brands'])];
            $type = $context['types'][array_rand($context['types'])];
            $variety = ['Reserva', 'Premium', 'Black Label', 'Gold', 'Extra Fruit', 'Zero', 'Original'];
            
            $products[] = [
                'name' => "$brand $type " . $variety[array_rand($variety)],
                'brand' => $brand,
                'base_price' => rand($context['min'], $context['max'])
            ];
        }

        return $products;
    }

    private function getContextByKeyword($name): array
    {
        $n = strtolower($name);
        if (str_contains($n, 'cerveza')) return ['brands' => ['Paceña', 'Huari', 'Corona'], 'types' => ['Lager', 'Pilsener'], 'min' => 12, 'max' => 20];
        if (str_contains($n, 'gaseosa')) return ['brands' => ['Coca Cola', 'Pepsi', 'Fanta'], 'types' => ['2L', '500ml', 'Retornable'], 'min' => 7, 'max' => 15];
        if (str_contains($n, 'whisky')) return ['brands' => ['Johnnie Walker', 'Chivas Regal'], 'types' => ['12 Años', 'Red Label'], 'min' => 180, 'max' => 450];
        if (str_contains($n, 'ron')) return ['brands' => ['Abuelo', 'Flor de Caña', 'Havana'], 'types' => ['Añejo', '7 Años'], 'min' => 60, 'max' => 120];
        if (str_contains($n, 'jugo')) return ['brands' => ['Del Valle', 'Tampico'], 'types' => ['Naranja', 'Durazno'], 'min' => 10, 'max' => 18];
        if (str_contains($n, 'agua')) return ['brands' => ['Vital', 'Villa Santa'], 'types' => ['Sin Gas', 'Con Gas'], 'min' => 6, 'max' => 12];
        
        return ['brands' => ['Marca Genérica'], 'types' => ['Estándar'], 'min' => 10, 'max' => 50];
    }
}