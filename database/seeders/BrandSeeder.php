<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Brand;
use App\Models\Provider;
use Illuminate\Support\Str; // <--- Importante

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $cbn = Provider::where('commercial_name', 'CBN')->first();
        $embol = Provider::where('commercial_name', 'like', '%Coca-Cola%')->first();
        $dym = Provider::where('commercial_name', 'like', 'D&M%')->first();
        $casaReal = Provider::where('commercial_name', 'Casa Real')->first();

        $brands = [
            ['name' => 'Paceña', 'provider_id' => $cbn?->id],
            ['name' => 'Huari', 'provider_id' => $cbn?->id],
            ['name' => 'Corona', 'provider_id' => $cbn?->id],
            ['name' => 'Coca-Cola', 'provider_id' => $embol?->id],
            ['name' => 'Johnnie Walker', 'provider_id' => $dym?->id],
            ['name' => 'Fernet Branca', 'provider_id' => $dym?->id],
            ['name' => 'Casa Real', 'provider_id' => $casaReal?->id],
        ];

        foreach ($brands as $data) {
            Brand::firstOrCreate(
                ['name' => $data['name']],
                [
                    'slug' => Str::slug($data['name']), // <--- ESTO FALTABA
                    'provider_id' => $data['provider_id'],
                    'is_active' => true
                ]
            );
        }
    }
}