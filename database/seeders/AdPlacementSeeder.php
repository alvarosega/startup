<?php

namespace Database\Seeders;

use App\Models\AdPlacement;
use Illuminate\Database\Seeder;

class AdPlacementSeeder extends Seeder
{
    public function run(): void
    {
        $placements = [
            [
                'name' => 'Home Hero Carousel',
                'code' => 'HOME_HERO',
                'max_items' => 5,
            ],
            [
                'name' => 'Search Results Top',
                'code' => 'SEARCH_TOP',
                'max_items' => 2,
            ],
            [
                'name' => 'Checkout Impulse',
                'code' => 'CHECKOUT_IMPULSE',
                'max_items' => 3,
            ]
        ];

        foreach ($placements as $placement) {
            AdPlacement::updateOrCreate(
                ['code' => $placement['code']],
                [
                    'name' => $placement['name'],
                    'max_items' => $placement['max_items'],
                    'is_active' => true,
                ]
            );
        }
    }
}