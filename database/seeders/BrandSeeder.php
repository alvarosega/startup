<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Brand;
use App\Models\Provider;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        
        // Recuperamos proveedores para asignar marcas
        $providersIds = Provider::pluck('id')->toArray();
        
        // Helpers para tus marcas reales
        $cbn = Provider::where('commercial_name', 'CBN')->first();
        $embol = Provider::where('commercial_name', 'like', '%Coca-Cola%')->first();
        $dym = Provider::where('commercial_name', 'like', 'D&M%')->first();
        $casaReal = Provider::where('commercial_name', 'Casa Real')->first();

        // 1. MARCAS REALES
        $brands = [
            ['name' => 'Paceña', 'provider_id' => $cbn?->id],
            ['name' => 'Huari', 'provider_id' => $cbn?->id],
            ['name' => 'Corona', 'provider_id' => $cbn?->id],
            ['name' => 'Coca-Cola', 'provider_id' => $embol?->id],
            ['name' => 'Johnnie Walker', 'provider_id' => $dym?->id],
            ['name' => 'Fernet Branca', 'provider_id' => $dym?->id],
            ['name' => 'Casa Real', 'provider_id' => $casaReal?->id],
        ];

        // 2. MARCAS FICTICIAS (Generación Masiva)
        // Generamos nombres de bebidas creíbles
        for ($i = 0; $i < 30; $i++) {
            $brands[] = [
                'name' => ucfirst($faker->unique()->word) . ' ' . $faker->suffix, // Ej: "Luna Ltd", "Sol Group"
                'provider_id' => $faker->randomElement($providersIds)
            ];
        }

        foreach ($brands as $data) {
            Brand::firstOrCreate(
                ['name' => $data['name']],
                [
                    'slug' => Str::slug($data['name']),
                    // CORRECCIÓN: Usar el valor directo
                    'provider_id' => $data['provider_id'],
                    'is_active' => true
                ]
            );
        }
    }
}