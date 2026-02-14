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

        // =========================================================================
        // SILO 1: ADMIN (Guard: 'admin')
        // =========================================================================
        
        // Listado de Permisos para el ERP
        $adminPermissions = [
            'view_admin_dashboard',
            'manage_settings',
            'manage_catalog',
            'view_inventory',
            'create_purchase',
            'manage_transfers',
            'manage_transformations',
            'request_removal',
            'approve_removal',
            'audit_identity',
            'view_analytics',
        ];

        // Crear permisos EXPECÍFICAMENTE para el guard 'admin'
        foreach ($adminPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'admin']);
        }

        // --- ROLES ADMIN ---

        // A. SUPER ADMIN
        $roleSuper = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'admin']);
        // El Super Admin obtiene TODOS los permisos del guard 'admin'
        $roleSuper->syncPermissions(Permission::where('guard_name', 'admin')->get());

        // B. BRANCH ADMIN
        $roleBranch = Role::firstOrCreate(['name' => 'branch_admin', 'guard_name' => 'admin']);
        $roleBranch->syncPermissions([
            'view_admin_dashboard', 'view_inventory', 'create_purchase', 
            'manage_transfers', 'request_removal', 'manage_transformations'
        ]);

        // C. LOGISTICS MANAGER
        $roleLogistics = Role::firstOrCreate(['name' => 'logistics_manager', 'guard_name' => 'admin']);
        $roleLogistics->syncPermissions([
            'view_admin_dashboard', 'manage_catalog', 'view_inventory', 
            'manage_transfers', 'approve_removal'
        ]);

        // D. FINANCE MANAGER
        $roleFinance = Role::firstOrCreate(['name' => 'finance_manager', 'guard_name' => 'admin']);
        $roleFinance->syncPermissions([
            'view_admin_dashboard', 'approve_removal', 'view_analytics'
        ]);

        // E. INVENTORY MANAGER
        $roleInventory = Role::firstOrCreate(['name' => 'inventory_manager', 'guard_name' => 'admin']);
        $roleInventory->syncPermissions([
            'view_admin_dashboard', 'view_inventory', 'manage_transformations', 
            'request_removal', 'create_purchase'
        ]);

        // F. OTHERS
        $roleGrowth = Role::firstOrCreate(['name' => 'growth_specialist', 'guard_name' => 'admin']);
        $roleGrowth->syncPermissions(['view_admin_dashboard', 'view_analytics']);

        $roleIdentity = Role::firstOrCreate(['name' => 'identity_auditor', 'guard_name' => 'admin']);
        $roleIdentity->syncPermissions(['view_admin_dashboard', 'audit_identity']);

        $roleOperator = Role::firstOrCreate(['name' => 'logistics_operator', 'guard_name' => 'admin']);
        $roleOperator->syncPermissions(['view_admin_dashboard', 'manage_transformations', 'view_inventory']);


        // =========================================================================
        // SILO 2: DRIVER (Guard: 'driver')
        // =========================================================================

        $driverPermissions = [
            'access_driver_app',
            'manage_deliveries'
        ];

        foreach ($driverPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'driver']);
        }

        $roleDriver = Role::firstOrCreate(['name' => 'driver', 'guard_name' => 'driver']);
        $roleDriver->syncPermissions(['access_driver_app', 'manage_deliveries']);


        // =========================================================================
        // SILO 3: CUSTOMER (Guard: 'web')
        // =========================================================================
        
        // El cliente generalmente no tiene permisos granulares complejos, solo el rol
        Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);
    }
}