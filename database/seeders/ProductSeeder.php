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
        $subCategories = Category::whereNotNull('parent_id')->get();

        foreach ($subCategories as $category) {
            $productsData = $this->generateRealProducts($category->name, 4);

            foreach ($productsData as $prodData) {
                // CORRECCIÓN: Buscamos por Slug para evitar duplicados si el nombre varía ligeramente
                $slug = Str::slug($prodData['brand']);
                
                $brand = Brand::firstOrCreate(
                    ['slug' => $slug], // 1. Buscamos por la llave única
                    ['name' => $prodData['brand']] // 2. Si no existe, usamos este nombre
                );

                $product = Product::firstOrCreate([
                    'name' => $prodData['name']
                ], [
                    'brand_id' => $brand->id,
                    'category_id' => $category->id,
                    'slug' => Str::slug($prodData['name']) . '-' . Str::random(4),
                    'description' => "Producto original " . $prodData['name'],
                    'is_active' => true,
                    'is_alcoholic' => $category->requires_age_check
                ]);

                $this->createSkusForProduct($product, $prodData['base_price']);
            }
        }
    }

    private function createSkusForProduct($product, $basePrice)
    {
        $variations = [
            ['suffix' => 'Unitario', 'factor' => 1, 'price_mult' => 1.0, 'code_suf' => 'UNI'],
            ['suffix' => 'Pack x6', 'factor' => 6, 'price_mult' => 5.8, 'code_suf' => 'PK6'], 
            ['suffix' => 'Caja x12', 'factor' => 12, 'price_mult' => 11.5, 'code_suf' => 'C12'],
            ['suffix' => 'Caja x24', 'factor' => 24, 'price_mult' => 22.0, 'code_suf' => 'C24'],
            ['suffix' => 'Edición Especial', 'factor' => 1, 'price_mult' => 1.2, 'code_suf' => 'ESP']
        ];

        foreach ($variations as $var) {
            $skuName = $product->name . ' - ' . $var['suffix'];
            $price = $basePrice * $var['price_mult'];

            // Hash determinista para evitar colisiones de SKU sin usar random()
            $uniqueHash = strtoupper(substr(md5($product->id . $var['suffix']), 0, 8));
            $skuCode = "SKU-{$var['code_suf']}-{$uniqueHash}";

            $sku = Sku::firstOrCreate([
                'product_id' => $product->id,
                'name' => $skuName
            ], [
                'code' => $skuCode,
                'base_price' => (float) $price,
                'conversion_factor' => $var['factor'],
                'weight' => rand(100, 2000) / 1000,
                'is_active' => true
            ]);
            
            if(method_exists($sku, 'updatePrice')) {
                $sku->updatePrice((float) $price);
            }
        }
    }

    private function generateRealProducts($categoryName, $qty)
    {
        $context = $this->getContextByKeyword($categoryName);
        $products = [];

        for ($i = 0; $i < $qty; $i++) {
            $brand = $context['brands'][array_rand($context['brands'])];
            $type = $context['types'][array_rand($context['types'])];
            
            $variety = ['Clásico', 'Reserva', 'Extra', 'Gold', 'Silver', 'Black', 'Especial', 'Premium', 'Selection'];
            $varName = $variety[array_rand($variety)];

            $fullName = "$brand $type $varName";
            
            $products[] = [
                'name' => $fullName,
                'brand' => $brand,
                'base_price' => rand($context['min'], $context['max'])
            ];
        }

        return $products;
    }

    private function getContextByKeyword($name)
    {
        $n = strtolower($name);

        if (str_contains($n, 'cerveza')) return [
            'brands' => ['Paceña', 'Huari', 'Ducal', 'Corona', 'Heineken', 'Patagonia'],
            'types' => ['Pilsener', 'Lager', 'Stout', 'Trigo', 'IPA'],
            'min' => 10, 'max' => 25
        ];
        if (str_contains($n, 'singani')) return [
            'brands' => ['Casa Real', 'Los Parrales', 'Rujero', 'San Pedro'],
            'types' => ['Etiqueta Negra', 'Gran Singani', 'Tradicional', 'Aniversario'],
            'min' => 40, 'max' => 150
        ];
        if (str_contains($n, 'whisky')) return [
            'brands' => ['Johnnie Walker', 'Chivas Regal', 'Jack Daniels', 'Buchanans', 'Old Parr'],
            'types' => ['Red Label', 'Black Label', '12 Años', '18 Años', 'Honey'],
            'min' => 150, 'max' => 800
        ];
        if (str_contains($n, 'ron')) return [
            'brands' => ['Flor de Caña', 'Havana Club', 'Abuelo', 'Bacardi'],
            'types' => ['4 Años', '7 Años', 'Gran Reserva', 'Añejo', 'Blanco'],
            'min' => 50, 'max' => 200
        ];
        if (str_contains($n, 'vodka')) return [
            'brands' => ['Absolut', 'Smirnoff', 'Grey Goose', 'Skyy', 'Stolichnaya'],
            'types' => ['Blue', 'Raspberry', 'Citron', 'Vainilla', 'Original'],
            'min' => 60, 'max' => 250
        ];
        if (str_contains($n, 'vino')) return [
            'brands' => ['Kohlberg', 'Aranjuez', 'Campos de Solana', 'Concha y Toro'],
            'types' => ['Tannat', 'Cabernet', 'Syrah', 'Merlot', 'Chardonnay'],
            'min' => 35, 'max' => 120
        ];
        if (str_contains($n, 'gaseosa') || str_contains($n, 'cola')) return [
            'brands' => ['Coca Cola', 'Pepsi', 'Sprite', 'Fanta', '7Up'],
            'types' => ['Original', 'Zero', 'Light', 'Sabor Intenso'],
            'min' => 8, 'max' => 15
        ];
        if (str_contains($n, 'agua')) return [
            'brands' => ['Vital', 'Villa Santa', 'Naturagua', 'Perrier'],
            'types' => ['Pura', 'Con Gas', 'Mineral', 'Alcalina'],
            'min' => 5, 'max' => 12
        ];
        if (str_contains($n, 'energizante')) return [
            'brands' => ['Red Bull', 'Monster', 'Ciclon', 'V220'],
            'types' => ['Original', 'Sugar Free', 'Tropical', 'Coffee'],
            'min' => 12, 'max' => 25
        ];
        if (str_contains($n, 'jugo') || str_contains($n, 'nectar')) return [
            'brands' => ['Del Valle', 'Tampico', 'Kris', 'Frux'],
            'types' => ['Naranja', 'Durazno', 'Manzana', 'Piña'],
            'min' => 10, 'max' => 20
        ];
        if (str_contains($n, 'snack') || str_contains($n, 'papas')) return [
            'brands' => ['Pringles', 'Lays', 'Kris', 'Doritos'],
            'types' => ['Original', 'Picante', 'Queso', 'Cebolla'],
            'min' => 15, 'max' => 30
        ];
        if (str_contains($n, 'cigarro') || str_contains($n, 'tabaco')) return [
            'brands' => ['Marlboro', 'Camel', 'L&M', 'Lucky Strike'],
            'types' => ['Rojo', 'Gold', 'Ice Blast', 'Double Click'],
            'min' => 20, 'max' => 40
        ];
        if (str_contains($n, 'licores') || str_contains($n, 'crema')) return [
            'brands' => ['Baileys', 'Amarula', 'Kahlua', 'Frangelico'],
            'types' => ['Original', 'Chocolate', 'Coffee', 'Cream'],
            'min' => 100, 'max' => 200
        ];

        return [
            'brands' => ['Marca Genérica'],
            'types' => ['Producto Estándar'],
            'min' => 10, 'max' => 50
        ];
    }
}
