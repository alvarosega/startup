<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Throwable;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $filePath = database_path('data/admins.csv');
        
        if (!file_exists($filePath)) {
            $this->command->error("ARCHIVO NO ENCONTRADO: {$filePath}.");
            return;
        }

        $file = fopen($filePath, 'r');
        $rawHeaders = fgetcsv($file, 0, ',');
        
        if (!$rawHeaders) {
            $this->command->error("CRÍTICO: El archivo CSV está vacío.");
            fclose($file);
            return;
        }

        $headers = array_map(fn($h) => strtolower(preg_replace('/^[\xef\xbb\xbf]+/', '', trim((string)$h))), $rawHeaders);

        // CORRECCIÓN LÓGICA: Alineación estricta con RolesAndPermissionsSeeder
        // Se asegura de que el nombre sea 'super_admin' y el guard 'super_admin'
        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'super_admin']);

        DB::beginTransaction();

        try {
            $rowNumber = 1;

            while (($data = fgetcsv($file, 0, ',')) !== false) {
                $rowNumber++;
                if (empty(array_filter($data))) continue;

                if (count($headers) !== count($data)) {
                    $this->command->error("ERROR DE ESTRUCTURA en fila {$rowNumber}. Las columnas no coinciden.");
                    continue;
                }

                $row = array_combine($headers, $data);

                $cleanRow = array_map(function($value) {
                    $str = trim((string)$value);
                    return $str === '' ? null : mb_convert_encoding($str, 'UTF-8', 'UTF-8');
                }, $row);

                $admin = Admin::updateOrCreate(
                    ['email' => $cleanRow['email']],
                    [
                        'first_name' => $cleanRow['first_name'],
                        'last_name'  => $cleanRow['last_name'],
                        'phone'      => $cleanRow['phone'],
                        'password'   => Hash::make($cleanRow['password']),
                        'branch_id'  => null, 
                        'is_active'  => (bool)($cleanRow['is_active'] ?? 1),
                        'mfa_secret' => null,
                    ]
                );

                if (!empty($cleanRow['role_name'])) {
                    // Spatie requiere que especifiquemos el guard si el modelo Admin 
                    // no tiene 'super_admin' como guard por defecto en config/auth.php
                    $roleToAssign = Role::where('name', $cleanRow['role_name'])
                                        ->where('guard_name', 'super_admin')
                                        ->first();

                    if ($roleToAssign) {
                        $admin->syncRoles([$roleToAssign]);
                    } else {
                        throw new \Exception("Fila {$rowNumber}: El rol '{$cleanRow['role_name']}' no existe en el guard 'super_admin'.");
                    }
                }
            }

            DB::commit();
            fclose($file);
            $this->command->info('Proceso de sembrado de SuperAdmins finalizado y sincronizado con Spatie.');

        } catch (Throwable $e) {
            DB::rollBack();
            if (is_resource($file)) {
                fclose($file);
            }
            $this->command->error("FALLO CRÍTICO en SuperAdminSeeder: " . $e->getMessage());
            throw $e;
        }
    }
}