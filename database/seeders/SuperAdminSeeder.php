<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $csvPath = database_path('data/admins.csv');

        if (!file_exists($csvPath)) {
            $this->command->error("Archivo CSV no encontrado en: $csvPath");
            return;
        }

        // El rol debe existir previamente (creado en RolesAndPermissionsSeeder)
        $role = Role::where('name', 'super_admin')->where('guard_name', 'super_admin')->first();

        if (!$role) {
            $this->command->error("El rol 'super_admin' con guard 'super_admin' no existe. Abortando.");
            return;
        }

        $file = fopen($csvPath, 'r');
        fgetcsv($file, 0, ";"); // Omitir cabecera

        while (($row = fgetcsv($file, 0, ";")) !== FALSE) {
            if (empty($row[3])) continue; // Validar email

            $admin = Admin::updateOrCreate(
                ['email' => $row[3]],
                [
                    'first_name' => $row[0],
                    'last_name'  => $row[1],
                    'phone'      => $row[2],
                    'password'   => $row[4], // Asumiendo cast 'hashed' en el modelo
                    'is_active'  => (bool)$row[5],
                ]
            );

            // Asignación de rol mediante Spatie
            if (!$admin->hasRole('super_admin', 'super_admin')) {
                $admin->assignRole($role);
                $this->command->info("Admin {$admin->email} creado y rol asignado.");
            } else {
                $this->command->comment("Admin {$admin->email} ya tenía el rol asignado.");
            }
        }

        fclose($file);
    }
}