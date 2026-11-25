<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar caché de permisos al iniciar
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // --- SILO 1: ADMINISTRACIÓN (Guard: super_admin) ---
        $adminPerms = [
            'view_admin_dashboard',
            'manage_users',
            'manage_drivers',
            'manage_catalog',
            'manage_branches',
            'manage_branch_schedules',
            'manage_branch_cash',
            'manage_inventory',
            'manage_prices',
            'manage_purchases',
            'manage_removals',
            'manage_orders',
            'manage_dispatch',
            'view_logistics_radar',
        ];

        foreach ($adminPerms as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'super_admin']);
        }

        // 1. Rol: Administrador Maestro (Super Admin - Alcance Global)
        $roleSuper = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'super_admin']);
        $roleSuper->syncPermissions($adminPerms);

        // 2. Rol: Administrador de Sucursal (Branch Manager)
        $roleBranchManager = Role::firstOrCreate(['name' => 'branch_manager', 'guard_name' => 'super_admin']);
        $roleBranchManager->syncPermissions([
            'view_admin_dashboard',
            'manage_catalog',
            'manage_branch_schedules',
            'manage_branch_cash',
            'manage_inventory',
            'manage_prices',
            'manage_purchases',
            'manage_removals',
            'manage_orders',
            'manage_dispatch',
            'view_logistics_radar',
            'manage_drivers',
        ]);

        // 3. Rol: Operador de Almacén / Stock (Branch Warehouse)
        $roleBranchWarehouse = Role::firstOrCreate(['name' => 'branch_warehouse', 'guard_name' => 'super_admin']);
        $roleBranchWarehouse->syncPermissions([
            'view_admin_dashboard',
            'manage_inventory',
            'manage_purchases',
            'manage_removals',
        ]);

        // 4. Rol: Operador de Despacho / Logística (Branch Logistics)
        $roleBranchLogistics = Role::firstOrCreate(['name' => 'branch_logistics', 'guard_name' => 'super_admin']);
        $roleBranchLogistics->syncPermissions([
            'view_admin_dashboard',
            'manage_orders',
            'manage_dispatch',
            'view_logistics_radar',
            'manage_drivers',
        ]);

        // 5. Rol: Operador de Caja (Branch Cashier)
        $roleBranchCashier = Role::firstOrCreate(['name' => 'branch_cashier', 'guard_name' => 'super_admin']);
        $roleBranchCashier->syncPermissions([
            'view_admin_dashboard',
            'manage_branch_cash',
        ]);


        // --- SILO 2: LOGÍSTICA (Guard: driver) ---
        $driverPerms = [
            'access_driver_app',
            'update_delivery_status'
        ];

        foreach ($driverPerms as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'driver']);
        }

        $roleDriver = Role::firstOrCreate(['name' => 'driver', 'guard_name' => 'driver']);
        $roleDriver->syncPermissions($driverPerms);


        // --- SILO 3: CLIENTES (Guard: customer) ---
        // Por ahora sin permisos específicos, solo el rol
        Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'customer']);
        
        $this->command->info('Roles y permisos granulares creados correctamente.');
    }
}