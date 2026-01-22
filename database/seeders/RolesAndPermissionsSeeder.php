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
        // 1. Limpiar caché de permisos
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Definir Permisos del Sistema (Granularidad)
        $permissions = [
            'view_admin_dashboard',   // Acceso base al panel ERP
            
            // --- NUEVO: Permisos de Conductor ---
            'access_driver_app',      // Acceso a la interfaz móvil de conductor
            'manage_deliveries',      // Ver ruta, marcar entregado, subir foto
            // ------------------------------------

            // Configuración Global
            'manage_settings',        
            
            // Catálogo (Logística Global)
            'manage_catalog',         
            
            // Operaciones de Inventario
            'view_inventory',         
            'create_purchase',        
            'manage_transfers',       
            'manage_transformations', 
            
            // Auditoría y Bajas
            'request_removal',        
            'approve_removal',        
            
            // Especiales
            'audit_identity',         
            'view_analytics',         
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 3. DEFINICIÓN DE ROLES

        // A. SUPER ADMIN
        $roleSuper = Role::firstOrCreate(['name' => 'super_admin']);
        $roleSuper->syncPermissions(Permission::all());

        // B. BRANCH ADMIN
        $roleBranch = Role::firstOrCreate(['name' => 'branch_admin']);
        $roleBranch->syncPermissions([
            'view_admin_dashboard',
            'view_inventory',
            'create_purchase',
            'manage_transfers',
            'request_removal',
            'manage_transformations'
        ]);

        // C. LOGISTICS MANAGER
        $roleLogistics = Role::firstOrCreate(['name' => 'logistics_manager']);
        $roleLogistics->syncPermissions([
            'view_admin_dashboard',
            'manage_catalog',
            'view_inventory',
            'manage_transfers',
            'approve_removal'
        ]);

        // D. FINANCE MANAGER
        $roleFinance = Role::firstOrCreate(['name' => 'finance_manager']);
        $roleFinance->syncPermissions([
            'view_admin_dashboard',
            'approve_removal',
            'view_analytics'
        ]);

        // E. INVENTORY MANAGER
        $roleInventory = Role::firstOrCreate(['name' => 'inventory_manager']);
        $roleInventory->syncPermissions([
            'view_admin_dashboard',
            'view_inventory',
            'manage_transformations',
            'request_removal',
            'create_purchase'
        ]);

        // F. GROWTH SPECIALIST
        $roleGrowth = Role::firstOrCreate(['name' => 'growth_specialist']);
        $roleGrowth->syncPermissions([
            'view_admin_dashboard',
            'view_analytics'
        ]);

        // G. IDENTITY AUDITOR
        $roleIdentity = Role::firstOrCreate(['name' => 'identity_auditor']);
        $roleIdentity->syncPermissions([
            'view_admin_dashboard',
            'audit_identity'
        ]);

        // H. LOGISTICS OPERATOR
        $roleOperator = Role::firstOrCreate(['name' => 'logistics_operator']);
        $roleOperator->syncPermissions([
            'view_admin_dashboard',
            'manage_transformations',
            'view_inventory'
        ]);

        // I. DRIVER (NUEVO)
        $roleDriver = Role::firstOrCreate(['name' => 'driver']);
        $roleDriver->syncPermissions([
            'access_driver_app',
            'manage_deliveries'
        ]);

        // J. CUSTOMER
        Role::firstOrCreate(['name' => 'customer']);
    }
}