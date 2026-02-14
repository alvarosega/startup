<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. CIMIENTOS (Roles y Permisos con Guards correctos)
        $this->call(RolesAndPermissionsSeeder::class);

        // 2. INFRAESTRUCTURA (Sucursales)
        $this->call(BranchSeeder::class);

        // 3. ACTORES (Silos Aislados)
        // Crea el Super Admin en la tabla 'admins'
        $this->call(SuperAdminSeeder::class); 
        
        // (Opcional) Si quieres crear clientes de prueba
        // $this->call(CustomerSeeder::class); 

        // 4. CATÁLOGOS Y OPERACIONES (Mantener si estos modelos existen)
        $this->call([
             LevelSeeder::class, // Verificar si 'levels' existe en nueva BD
             ComplianceSeeder::class,
             ProviderSeeder::class,
             BrandSeeder::class,
             CategorySeeder::class,
             ProductSeeder::class, 
             InventorySeeder::class
        ]);
        $this->call(MarketZoneSeeder::class);
    }
}