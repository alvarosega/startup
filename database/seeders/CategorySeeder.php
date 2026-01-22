<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category; // Modelo Actualizado
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // A. CERVEZAS
        $cervezas = Category::create([
            'name' => 'Cervezas', 
            'slug' => 'cervezas', 
            'is_active' => true,
            'requires_age_check' => true
        ]);
        $this->createChildren($cervezas, ['Rubias / Lager', 'Trigo', 'Artesanales', 'Importadas']);

        // B. DESTILADOS
        $destilados = Category::create([
            'name' => 'Destilados', 
            'slug' => 'destilados', 
            'requires_age_check' => true
        ]);
        $this->createChildren($destilados, ['Singani', 'Whisky', 'Ron', 'Vodka', 'Gin', 'Fernet']);

        // C. BEBIDAS & MIXERS
        $sinAlcohol = Category::create([
            'name' => 'Bebidas & Mixers', 
            'slug' => 'bebidas-mixers', 
            'requires_age_check' => false
        ]);
        $this->createChildren($sinAlcohol, ['Gaseosas', 'Energizantes', 'Aguas', 'Jugos']);

        // D. VINOS
        $vinos = Category::create([
            'name' => 'Vinos', 
            'slug' => 'vinos', 
            'requires_age_check' => true
        ]);
        $this->createChildren($vinos, ['Tintos', 'Blancos', 'Rosados']);
    }

    private function createChildren(Category $padre, array $hijos)
    {
        foreach ($hijos as $nombreHijo) {
            Category::firstOrCreate([
                'name' => $nombreHijo,
                'slug' => Str::slug($nombreHijo),
                'parent_id' => $padre->id,
                'requires_age_check' => $padre->requires_age_check ?? false 
            ]);
        }
    }
}