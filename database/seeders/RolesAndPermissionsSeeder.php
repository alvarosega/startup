<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // 1. Limpiar caché
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Definir Permisos (Granularidad)
        $permissions = [
            'view_admin_dashboard', // Acceso al panel
            
            // Configuración Global (Solo Super Admin)
            'manage_settings',      // Sucursales, Usuarios globales
            
            // Catálogo (Logística)
            'manage_catalog',       // Crear productos, marcas, proveedores

            // Operaciones Diarias
            'view_inventory',       // Ver Kardex
            'create_purchase',      // Compras
            'manage_transfers',     // Enviar/Recibir
            'manage_transformations', // Unpacking
            
            // Auditoría
            'request_removal',      // Solicitar baja
            'approve_removal',      // Aprobar baja
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 3. ROLES

        // A. Inventory Manager (Operativo Local)
        $inventoryRole = Role::firstOrCreate(['name' => 'inventory_manager']);
        $inventoryRole->syncPermissions([
            'view_admin_dashboard',
            'view_inventory',       // Podrá entrar, pero el controller filtrará por su branch_id
            'create_purchase',
            'manage_transfers',
            'manage_transformations',
            'request_removal',
        ]);

        // B. Logistics Manager (Jefe de Operaciones)
        $logisticsRole = Role::firstOrCreate(['name' => 'logistics_manager']);
        $logisticsRole->syncPermissions([
            'view_admin_dashboard',
            'manage_catalog',
            'view_inventory',
            'create_purchase',
            'manage_transfers',
            'manage_transformations',
            'request_removal',
            'approve_removal',
        ]);

        // C. Super Admin
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdmin->givePermissionTo(Permission::all());
    }
}