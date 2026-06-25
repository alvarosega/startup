<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Operations\Branch;
use Spatie\Permission\Models\Role;
use Exception;
use Throwable;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $csvPath = database_path('data/customers.csv');

        if (!file_exists($csvPath)) {
            $this->command->error("ARCHIVO NO ENCONTRADO: $csvPath");
            return;
        }

        $branches = Branch::where('is_active', true)->get();
        if ($branches->isEmpty()) {
            $this->command->error("No hay sucursales activas. Abortando.");
            return;
        }

        // El guard se cambia a 'customer' según la configuración defaults de su config/auth.php
        $customerRole = Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'customer']);
        $password = Hash::make('password');
        
        $file = fopen($csvPath, 'r');
        $rawHeaders = fgetcsv($file, 0, ";");
        
        if (!$rawHeaders) {
            $this->command->error("CRÍTICO: CSV de clientes vacío o ilegible.");
            fclose($file);
            return;
        }

        // Normalización de cabeceras para mapeo asociativo
        $headers = array_map(fn($h) => strtolower(preg_replace('/^[\xef\xbb\xbf]+/', '', trim((string)$h))), $rawHeaders);
        $count = 0;
        $rowNumber = 1;

        DB::beginTransaction();

        try {
            while (($rowData = fgetcsv($file, 0, ";")) !== FALSE) {
                $rowNumber++;
                
                if (empty(array_filter($rowData))) continue;

                if (count($headers) !== count($rowData)) {
                    throw new Exception("Error en fila {$rowNumber}: Número de columnas inconsistente con la cabecera.");
                }

                $row = array_combine($headers, array_map('trim', $rowData));

                if (empty($row['email']) || empty($row['phone'])) {
                    throw new Exception("Fila {$rowNumber}: Campos 'email' y 'phone' son mandatorios.");
                }

                $customerId = (string) Str::uuid();
                $branch = $branches->random();
                
                $lat = (float) $row['latitude'];
                $lng = (float) $row['longitude'];

                // Compilación de expresiones espaciales nativas WKT (Longitud Latitud)
                $locationExpression = DB::raw("ST_GeomFromText('POINT({$lng} {$lat})')");

                // 1. Inserción en Tabla Core (Customers)
                DB::table('customers')->insert([
                    'id'                    => $customerId,
                    'branch_id'             => $branch->id,
                    'phone'                 => '+591' . $row['phone'],
                    'country_code'          => 'BO',
                    'email'                 => $row['email'],
                    'password'              => $password,
                    'trust_score'           => 50,
                    'is_active'             => true,
                    'last_known_location'   => $locationExpression,
                    'created_at'            => now(),
                    'updated_at'            => now(),
                ]);

                // 2. Inserción en Tabla Profile (Customer Profiles)
                DB::table('customer_profiles')->insert([
                    'customer_id'   => $customerId,
                    'first_name'    => strtoupper($row['first_name']),
                    'last_name'     => strtoupper($row['last_name']),
                    'avatar_type'   => 'icon',
                    'avatar_source' => 'avatar_' . rand(1, 8) . '.png',
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);

                // 3. Inserción en Tabla de Direcciones (Customer Addresses)
                DB::table('customer_addresses')->insert([
                    'id'          => (string) Str::uuid(),
                    'customer_id' => $customerId,
                    'branch_id'   => $branch->id,
                    'alias'       => strtoupper($row['alias']),
                    'address'     => strtoupper($row['address']),
                    'position'    => $locationExpression,
                    'reference'   => strtoupper($row['reference']),
                    'is_default'  => true,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);

                // 4. Inserción de Relación Polimórfica de Roles (Spatie)
                DB::table('model_has_roles')->insert([
                    'role_id'    => $customerRole->id,
                    'model_type' => 'App\Models\Users\Customer', // Ajustado al namespace físico de config/auth.php
                    'model_id'   => $customerId,
                ]);

                $count++;
            }

            DB::commit();
            fclose($file);
            $this->command->info("✅ CustomerSeeder: $count registros importados de forma consistente.");

        } catch (Throwable $e) {
            DB::rollBack();
            if (is_resource($file)) fclose($file);
            $this->command->error("ABORTO EN FILA {$rowNumber}: " . $e->getMessage());
            throw $e;
        }
    }
}