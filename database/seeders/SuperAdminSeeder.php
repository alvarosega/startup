<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar caché de permisos por seguridad
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Crear el Rol Maestro (Guard: admin)
        $superAdminRole = Role::firstOrCreate(
            ['name' => 'super_admin', 'guard_name' => 'admin'],
            ['display_name' => 'Super Administrador Global']
        );

        // 2. Crear el Usuario Super Admin
        $admin = Admin::where('email', 'admin@admin.com')->first();

        if (! $admin) {
            $admin = Admin::create([
                // --- DATOS PERSONALES (Nuevos campos obligatorios) ---
                'first_name' => 'Super',
                'last_name'  => 'Admin',
                'phone'      => '+59170000000', // Opcional, pero recomendado
                
                // --- DATOS DE CUENTA ---
                'email'      => 'admin@admin.com',
                'password'   => 'password', // El modelo Admin hace el cast a 'hashed'
                'role_level' => 'super_admin',
                'is_active'  => true,
                'branch_id'  => null, // Null = Acceso Global
                'mfa_secret' => null,
            ]);
            
            $this->command->info('Super Admin creado con éxito (ID Binario generado).');
        } else {
            $this->command->warn('El Super Admin ya existe. Omitiendo creación.');
        }

        // 3. Asignar el Rol
        if (! $admin->hasRole('super_admin', 'admin')) {
            $admin->assignRole($superAdminRole);
            $this->command->info('Rol asignado correctamente.');
        }
    }
}