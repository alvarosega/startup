<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Level;

class LevelSeeder extends Seeder
{
    public function run(): void
    {
        $levels = [
            [
                'name' => 'Bronce',
                'min_points' => 0,
                'color_hex' => '#CD7F32',
                'benefits_json' => ['shipping_discount' => 0, 'priority_support' => false]
            ],
            [
                'name' => 'Plata',
                'min_points' => 1000,
                'color_hex' => '#C0C0C0',
                'benefits_json' => ['shipping_discount' => 5, 'priority_support' => false]
            ],
            [
                'name' => 'Oro',
                'min_points' => 5000,
                'color_hex' => '#FFD700',
                'benefits_json' => ['shipping_discount' => 10, 'priority_support' => true]
            ],
            [
                'name' => 'Diamante',
                'min_points' => 15000,
                'color_hex' => '#B9F2FF',
                'benefits_json' => ['shipping_discount' => 15, 'credit_envases' => 12]
            ],
            [
                'name' => 'Leyenda', // El nivel "Dios"
                'min_points' => 50000,
                'color_hex' => '#000000',
                'benefits_json' => ['shipping_discount' => 100, 'exclusive_events' => true]
            ]
        ];

        foreach ($levels as $level) {
            Level::updateOrCreate(['name' => $level['name']], $level);
        }
    }
}