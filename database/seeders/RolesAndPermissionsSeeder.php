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
            'manage_inventory',
            'manage_branches'
        ];

        foreach ($adminPerms as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'super_admin']);
        }

        // Crear Rol y asignar SOLO estos permisos iniciales
        $roleSuper = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'super_admin']);
        $roleSuper->syncPermissions($adminPerms);


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