<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Aseguramos que el rol exista antes de asignar
        $role = Role::where('name', 'super_admin')->where('guard_name', 'super_admin')->first();

        $admin = Admin::where('email', 'admin@admin.com')->first();

        if (!$admin) {
            $admin = Admin::create([
                'first_name' => 'Gerente',
                'last_name'  => 'General',
                'phone'      => '+59170000000',
                'email'      => 'admin@admin.com',
                'password'   => 'password', // El modelo Admin debe tener el cast 'hashed'
                'is_active'  => true,
            ]);
            $this->command->info('Usuario Gerente creado.');
        }

        // Asignación de rol
        if (!$admin->hasRole('super_admin', 'super_admin')) {
            $admin->assignRole($role);
            $this->command->info('Rol super_admin asignado con éxito.');
        }
    }
}