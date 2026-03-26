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
            CustomerSeeder::class,
            ComplianceSeeder::class,
            MarketZoneSeeder::class,    
            CategorySeeder::class,    
            ProviderSeeder::class,      
            BrandSeeder::class,         
            ProductSeeder::class,    
            SkuSeeder::class,
            PriceSeeder::class,
            InventorySeeder::class,
            BundleSeeder::class,
            AdPlacementSeeder::class,
            HeroBannerSeeder::class,
            CategoryBannerSeeder::class,
            BundleBannerSeeder::class,
        ]);
    }
}