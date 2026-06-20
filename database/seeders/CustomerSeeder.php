<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Branch;
use Spatie\Permission\Models\Role;

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

        $customerRole = Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);
        $password = Hash::make('password');
        
        $file = fopen($csvPath, 'r');
        fgetcsv($file, 0, ";"); // Omitir cabecera

        $count = 0;

        DB::transaction(function () use ($file, $branches, $customerRole, $password, &$count) {
            while (($row = fgetcsv($file, 0, ";")) !== FALSE) {
                $row = array_map('trim', $row);
                if (count($row) < 9 || empty($row[2])) continue;

                $customerId = (string) Str::uuid();
                $branch = $branches->random();
                
                // Mapeo: 0:fn, 1:ln, 2:email, 3:phone, 4:lat, 5:lng, 6:alias, 7:address, 8:ref
                $lat = floatval($row[4]);
                $lng = floatval($row[5]);

                // 1. Core
                DB::table('customers')->insert([
                    'id'           => $customerId,
                    'branch_id'    => $branch->id,
                    'phone'        => '+591' . $row[3],
                    'country_code' => 'BO',
                    'email'        => $row[2],
                    'password'     => $password,
                    'trust_score'  => 50,
                    'is_active'    => true,
                    'latitude'     => $lat,
                    'longitude'    => $lng,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);

                // 2. Profile
                DB::table('customer_profiles')->insert([
                    'customer_id'   => $customerId,
                    'first_name'    => strtoupper($row[0]),
                    'last_name'     => strtoupper($row[1]),
                    'avatar_source' => 'avatar_' . rand(1, 8) . '.png',
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);

                // 3. Address
                DB::table('customer_addresses')->insert([
                    'id'          => (string) Str::uuid(),
                    'customer_id' => $customerId,
                    'branch_id'   => $branch->id,
                    'alias'       => strtoupper($row[6]),
                    'address'     => strtoupper($row[7]),
                    'reference'   => strtoupper($row[8]),
                    'latitude'    => $lat,
                    'longitude'   => $lng,
                    'is_default'  => true,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);

                // 4. Role
                DB::table('model_has_roles')->insert([
                    'role_id'    => $customerRole->id,
                    'model_type' => 'App\Models\Customer',
                    'model_id'   => $customerId,
                ]);

                $count++;
            }
        });

        fclose($file);
        $this->command->info("✅ CustomerSeeder: $count registros importados.");
    }
}