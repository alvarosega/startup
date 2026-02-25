<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Product, Sku, Brand, Category, Branch, Price, Provider};
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $subCategories = Category::whereNotNull('parent_id')->get();
        $branches = Branch::all();

        // 1. CREACIÓN DE PROVEEDOR (Uso de tax_id como llave única)
        $defaultProvider = Provider::firstOrCreate(
            ['tax_id' => '999999999-0'], // Llave única real según tu migración
            [
                'company_name' => 'Distribuidora Global S.A.',
                'commercial_name' => 'Global Distribución',
                'email_orders' => 'pedidos@global.com', // Nombre de columna correcto
                'is_active' => true,
                'lead_time_days' => 2
            ]
        );

        foreach ($subCategories as $category) {
            $productsData = $this->generateRealProducts($category->name, 3);

            foreach ($productsData as $prodData) {
                DB::transaction(function () use ($prodData, $category, $branches, $defaultProvider) {
                    
                    // 2. GESTIÓN DE MARCA (Relacionada con Proveedor y Categoría)
                    $brand = Brand::updateOrCreate(
                        ['slug' => Str::slug($prodData['brand'])],
                        [
                            'name' => $prodData['brand'], 
                            'provider_id' => $defaultProvider->id, // Requerido por migración
                            'category_id' => $category->id,       // Requerido por migración
                            'is_active' => true
                        ]
                    );

                    // 3. GESTIÓN DE PRODUCTO
                    $product = Product::updateOrCreate(
                        [
                            'name' => $prodData['name'],
                            'brand_id' => $brand->id 
                        ],
                        [
                            'category_id' => $category->id,
                            'slug' => Str::slug($prodData['name']) . '-' . Str::random(4),
                            'description' => "Stock oficial suministrado por " . $defaultProvider->company_name,
                            'is_active' => true,
                            'is_alcoholic' => $category->requires_age_check // Sincronizado con CategorySeeder
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
        ];

        foreach ($variations as $var) {
            $skuCode = "777" . strtoupper(substr(md5($product->id . $var['code_suf']), 0, 10));

            $sku = Sku::updateOrCreate(
                ['code' => $skuCode],
                [
                    'product_id' => $product->id,
                    'name' => "{$product->name} ({$var['suffix']})",
                    'base_price' => $basePrice * $var['price_mult'],
                    'conversion_factor' => $var['factor'],
                    'weight' => rand(500, 2000) / 1000,
                    'is_active' => true
                ]
            );

            // Sembrar Matriz de Precios
            foreach ($branches as $branch) {
                Price::updateOrCreate(
                    [
                        'sku_id' => $sku->id,
                        'branch_id' => $branch->id,
                        'type' => 'regular'
                    ],
                    [
                        'list_price' => $sku->base_price * 1.15,
                        'final_price' => $sku->base_price,
                        'min_quantity' => 1,
                        'priority' => 1,
                        'valid_from' => now(),
                    ]
                );
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