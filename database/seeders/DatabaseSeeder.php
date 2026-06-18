<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            BranchSeeder::class,
            SuperAdminSeeder::class, 
            LevelSeeder::class,
            CustomerSeeder::class,
            ComplianceSeeder::class,
            MarketZoneSeeder::class,    
            CategorySeeder::class,    
            ProviderSeeder::class,      
            BrandSeeder::class,         
            ProductSeeder::class,    
            SkuSeeder::class,
            PriceSeeder::class,
            InventoryTestSeeder::class,
            BundleTestSeeder::class,
            AdPlacementSeeder::class,  
            AdCampaignSeeder::class,
        ]);
    }
}