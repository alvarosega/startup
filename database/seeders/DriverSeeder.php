<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Operations\Branch;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Throwable;

final class DriverSeeder extends Seeder
{
    public function run(): void
    {
        $csvPath = database_path('data/drivers.csv');

        if (!file_exists($csvPath)) {
            $this->command->error("ARCHIVO NO ENCONTRADO: {$csvPath}");
            return;
        }

        $branches = Branch::where('is_active', true)->get();
        if ($branches->isEmpty()) {
            $this->command->error("No hay sucursales activas en la base de datos. Abortando.");
            return;
        }

        $driverRole = Role::firstOrCreate(['name' => 'driver', 'guard_name' => 'driver']);
        $password = Hash::make('password');

        $file = fopen($csvPath, 'r');
        if ($file === false) {
            $this->command->error("No se pudo abrir el archivo CSV de conductores.");
            return;
        }

        $rawHeaders = fgetcsv($file, 0, ';');
        if (!$rawHeaders) {
            $this->command->error("CRÍTICO: CSV de conductores vacío o ilegible.");
            fclose($file);
            return;
        }

        /** @var array<int, string> $headers */
        $headers = array_map(fn($h) => strtolower(preg_replace('/^[\xef\xbb\xbf]+/', '', trim((string)$h))), $rawHeaders);
        $count = 0;
        $rowNumber = 1;

        DB::beginTransaction();

        try {
            while (($rowData = fgetcsv($file, 0, ';')) !== false) {
                $rowNumber++;

                if (empty(array_filter($rowData))) {
                    continue;
                }

                if (count($headers) !== count($rowData)) {
                    throw new Exception("Error en fila {$rowNumber}: Número de columnas no coincide con la cabecera.");
                }

                $row = array_combine($headers, array_map('trim', $rowData));

                if (empty($row['email']) || empty($row['phone'])) {
                    throw new Exception("Fila {$rowNumber}: Campos 'email' y 'phone' son mandatorios.");
                }

                $driverId = (string) Str::uuid7();
                $status = strtolower($row['status'] ?? 'pending');
                $isApproved = $status === 'approved';
                
                // Los conductores aprobados se vinculan a una sucursal activa; los demás quedan pendientes de asignación
                $branchId = $isApproved ? $branches->random()->id : null;

                // 1. Inserción Core en drivers
                DB::table('drivers')->insert([
                    'id'                     => $driverId,
                    'branch_id'              => $branchId,
                    'phone'                  => '+591' . preg_replace('/^\+?591/', '', $row['phone']),
                    'email'                  => strtolower($row['email']),
                    'password'               => $password,
                    'status'                 => $status,
                    'is_online'              => false,
                    'is_available'           => false,
                    'was_previously_deleted' => false,
                    'needs_password_change'  => false,
                    'deleted_epoch'          => 0,
                    'created_at'             => now(),
                    'updated_at'             => now(),
                ]);

                // 2. Inserción en driver_profiles
                DB::table('driver_profiles')->insert([
                    'driver_id'          => $driverId,
                    'first_name'         => strtoupper($row['first_name']),
                    'last_name'          => strtoupper($row['last_name']),
                    'license_number'     => strtoupper($row['license_number'] ?? ''),
                    'license_plate'      => strtoupper($row['license_plate'] ?? ''),
                    'vehicle_type'       => strtolower($row['vehicle_type'] ?? 'moto'),
                    'avatar_type'        => 'icon',
                    'avatar_source'      => 'avatar_' . rand(1, 8) . '.svg',
                    'rejection_reason'   => !empty($row['rejection_reason']) ? $row['rejection_reason'] : null,
                    'ci_front_path'      => "drivers/{$driverId}/documents/ci_mock.png",
                    'license_photo_path' => "drivers/{$driverId}/documents/license_mock.png",
                    'vehicle_photo_path' => null,
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]);

                // 3. Inserción de Rol de Spatie (Guard driver)
                DB::table('model_has_roles')->insert([
                    'role_id'    => $driverRole->id,
                    'model_type' => 'App\Models\Users\Driver',
                    'model_id'   => $driverId,
                ]);

                $count++;
            }

            DB::commit();
            fclose($file);
            $this->command->info("✅ DriverSeeder: {$count} conductores importados correctamente.");

        } catch (Throwable $e) {
            DB::rollBack();
            if (is_resource($file)) {
                fclose($file);
            }
            $this->command->error("ABORTO EN FILA {$rowNumber}: " . $e->getMessage());
            throw $e;
        }
    }
}