<?php 

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Brand;
use App\Models\Provider;
use App\Models\Category;
use Illuminate\Support\Str;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Recuperar Categorías (Asegúrate de haber corrido CategorySeeder antes)
        $cervezas = Category::where('name', 'like', '%Cerveza%')->first();
        $gaseosas = Category::where('name', 'like', '%Gaseosa%')->orWhere('name', 'like', '%Refresco%')->first();
        $licores  = Category::where('name', 'like', '%Licor%')->orWhere('name', 'like', '%Destilado%')->first();

        // 2. Recuperar Proveedores
        $cbn = Provider::where('commercial_name', 'CBN')->first();
        $embol = Provider::where('commercial_name', 'like', '%Coca-Cola%')->first();
        $dym = Provider::where('commercial_name', 'like', 'D&M%')->first();

        $brands = [
            ['name' => 'Paceña', 'provider' => $cbn, 'category' => $cervezas],
            ['name' => 'Coca-Cola', 'provider' => $embol, 'category' => $gaseosas],
            ['name' => 'Johnnie Walker', 'provider' => $dym, 'category' => $licores],
        ];

        foreach ($brands as $data) {
            if (!$data['provider'] || !$data['category']) continue;

            Brand::updateOrCreate(
                ['name' => $data['name']],
                [
                    'slug' => Str::slug($data['name']),
                    'provider_id' => $data['provider']->id,
                    'category_id' => $data['category']->id, // <--- CAMBIO OBLIGATORIO
                    'is_active' => true,
                ]
            );
        }
    }
}