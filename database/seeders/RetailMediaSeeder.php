<?php

namespace Database\Seeders;

use App\Models\{AdPlacement, AdCampaign, Provider};
use Illuminate\Database\Seeder;

class RetailMediaSeeder extends Seeder
{
    public function run(): void
    {
        AdPlacement::updateOrCreate(
            ['code' => 'HOME_HERO'],
            ['name' => 'Carrusel Principal Home', 'max_items' => 5, 'is_active' => true]
        );

        $provider = Provider::first() ?? Provider::create([
            'commercial_name' => 'Retail Media Internal',
            'is_active' => true
        ]);

        AdCampaign::updateOrCreate(
            ['name' => 'Campaña Global 2026'],
            [
                'provider_id' => $provider->id,
                'type' => 'INTERNAL',
                'starts_at' => now()->subMonth(),
                'ends_at' => now()->addYear(),
                'is_active' => true
            ]
        );
        
        $this->command->info('✅ Infraestructura Retail Media: [OK]');
    }
}