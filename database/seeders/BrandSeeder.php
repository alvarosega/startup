<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Brand;
use App\Models\Provider;
use Illuminate\Support\Str;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Recuperar proveedores clave (Búsqueda por commercial_name o tax_id)
        $cbn = Provider::where('commercial_name', 'CBN')->first();
        $embol = Provider::where('commercial_name', 'like', '%Coca-Cola%')->first();
        $dym = Provider::where('commercial_name', 'like', 'D&M%')->first();

        $brands = [
            ['name' => 'Paceña', 'provider_id' => $cbn?->id],
            ['name' => 'Huari', 'provider_id' => $cbn?->id],
            ['name' => 'Corona', 'provider_id' => $cbn?->id],
            ['name' => 'Coca-Cola', 'provider_id' => $embol?->id],
            ['name' => 'Johnnie Walker', 'provider_id' => $dym?->id],
            ['name' => 'Fernet Branca', 'provider_id' => $dym?->id],
        ];

        foreach ($brands as $data) {
            Brand::updateOrCreate(
                ['name' => $data['name']],
                [
                    'slug' => Str::slug($data['name']),
                    'provider_id' => $data['provider_id'], // Ahora sí existe la columna
                    'is_active' => true,
                    'image_path' => null, // Se gestionará vía Action/Upload
                    'website' => null
                ]
            );
        }
    }
}