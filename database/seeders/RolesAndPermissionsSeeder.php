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
            'view_admin_dashboard',   // Acceso base al panel
            
            // Configuración Global
            'manage_settings',        // Gestión de usuarios globales, configuraciones
            
            // Catálogo (Logística Global)
            'manage_catalog',         // ABM Productos, Marcas, Categorías
            
            // Operaciones de Inventario
            'view_inventory',         // Ver stock/kardex
            'create_purchase',        // Registrar compras (Ingresos)
            'manage_transfers',       // Enviar/Recibir entre sucursales
            'manage_transformations', // Desglose/Unpacking (Caja -> Unidad)
            
            // Auditoría y Bajas
            'request_removal',        // Solicitar baja/merma
            'approve_removal',        // Aprobar baja (Financiero)
            
            // Especiales
            'audit_identity',         // Verificación de KYC (Identity Auditor)
            'view_analytics',         // Métricas de crecimiento (Growth)
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 3. DEFINICIÓN DE ROLES

        // A. SUPER ADMIN (Acceso Total)
        $roleSuper = Role::firstOrCreate(['name' => 'super_admin']);
        $roleSuper->syncPermissions(Permission::all());

        // B. BRANCH ADMIN (Gerente de Sucursal)
        $roleBranch = Role::firstOrCreate(['name' => 'branch_admin']);
        $roleBranch->syncPermissions([
            'view_admin_dashboard',
            'view_inventory',
            'create_purchase',
            'manage_transfers',
            'request_removal',
            'manage_transformations'
        ]);

        // C. LOGISTICS MANAGER (Jefe de Logística Global)
        $roleLogistics = Role::firstOrCreate(['name' => 'logistics_manager']);
        $roleLogistics->syncPermissions([
            'view_admin_dashboard',
            'manage_catalog',        // Define qué se vende
            'view_inventory',        // Ve todo el stock
            'manage_transfers',      // Mueve stock global
            'approve_removal'        // Puede aprobar bajas operativas
        ]);

        // D. FINANCE MANAGER (Gerente Financiero)
        $roleFinance = Role::firstOrCreate(['name' => 'finance_manager']);
        $roleFinance->syncPermissions([
            'view_admin_dashboard',
            'approve_removal',       // Control de pérdidas
            'view_analytics'
        ]);

        // E. INVENTORY MANAGER (Encargado de Inventario Local)
        $roleInventory = Role::firstOrCreate(['name' => 'inventory_manager']);
        $roleInventory->syncPermissions([
            'view_admin_dashboard',
            'view_inventory',
            'manage_transformations',
            'request_removal',
            'create_purchase'
        ]);

        // F. GROWTH SPECIALIST (Analista de Crecimiento)
        $roleGrowth = Role::firstOrCreate(['name' => 'growth_specialist']);
        $roleGrowth->syncPermissions([
            'view_admin_dashboard',
            'view_analytics'
        ]);

        // G. IDENTITY AUDITOR (Auditor de Identidad/KYC)
        $roleIdentity = Role::firstOrCreate(['name' => 'identity_auditor']);
        $roleIdentity->syncPermissions([
            'view_admin_dashboard',
            'audit_identity'
        ]);

        // H. LOGISTICS OPERATOR (Operario de Almacén)
        $roleOperator = Role::firstOrCreate(['name' => 'logistics_operator']);
        $roleOperator->syncPermissions([
            'view_admin_dashboard', // Acceso limitado
            'manage_transformations', // Trabajo físico
            'view_inventory'
        ]);

        // I. CUSTOMER (Cliente Final)
        // No tiene permisos de dashboard administrativo por defecto
        Role::firstOrCreate(['name' => 'customer']);
    }
}