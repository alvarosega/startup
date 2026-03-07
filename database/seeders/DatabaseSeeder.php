<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        $this->call(BranchSeeder::class);
        $this->call(SuperAdminSeeder::class); 
        
        $this->call([
            LevelSeeder::class,
            ComplianceSeeder::class,
            // Nivel 0
            MarketZoneSeeder::class,    // Nivel 0
            CategorySeeder::class,    
            ProviderSeeder::class,        // Nivel 1
            BrandSeeder::class,         // Nivel 2
            ProductSeeder::class,    
            SkuSeeder::class,
            //InventorySeeder::class,
            BundleSeeder::class,
        ]);
    }
}