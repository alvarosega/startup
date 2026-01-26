<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Level;
use App\Models\Branch; // Importante
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // =================================================================
        // FASE 1: CIMIENTOS (Roles, Niveles, Catálogos Base)
        // =================================================================
        $this->call([
            RolesAndPermissionsSeeder::class,
            LevelSeeder::class,
            ComplianceSeeder::class,
            ProviderSeeder::class,
            BrandSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class, 
        ]);

        // =================================================================
        // FASE 2: INFRAESTRUCTURA (Sucursales)
        // =================================================================
        // Llamamos al Seeder dedicado que crea las 5 sucursales con polígonos
        $this->call([
            BranchSeeder::class,
        ]);

        // Recuperamos la sucursal principal (Sopocachi) para asignarla a los usuarios demo
        $mainBranch = Branch::where('name', 'Sede Central - La Paz (Sopocachi)')->first();

        // =================================================================
        // FASE 3: ACTORES (Usuarios y Roles)
        // =================================================================
        $roles = [
            'super_admin' => Role::where('name', 'super_admin')->first(),
            'branch_admin' => Role::where('name', 'branch_admin')->first(),
            'inventory_manager' => Role::where('name', 'inventory_manager')->first(),
        ];
        
        $levelBronce = Level::where('name', 'Bronce')->first();

        // A. Super Admin (Global - branch_id null)
        $this->createUser(
            '70000000', 'BO',
            'admin@bolivialogistics.com', 
            'Super', 'Admin', 
            null, 
            $roles['super_admin'], 
            $levelBronce
        );

        // B. Branch Admin (Asignado a Sede Central)
        $this->createUser(
            '70000001', 'BO',
            'branch@bolivialogistics.com', 
            'Gerente', 'Sucursal', 
            $mainBranch?->id, 
            $roles['branch_admin'], 
            $levelBronce
        );

        // C. Inventory Manager (Asignado a Sede Central)
        $this->createUser(
            '70000002', 'BO',
            'inventory@bolivialogistics.com', 
            'Jefe', 'Inventario', 
            $mainBranch?->id, 
            $roles['inventory_manager'], 
            $levelBronce
        );

        // =================================================================
        // FASE 4: OPERACIONES (Inventario)
        // =================================================================
        $this->call([
            InventorySeeder::class
        ]);
    }

    private function createUser($phone, $countryCode, $email, $first, $last, $branchId, $role, $level)
    {
        if (!$role) return;

        $formattedPhone = $phone;
        if ($countryCode === 'BO' && !str_starts_with($phone, '+')) {
            $formattedPhone = '+591' . $phone;
        }

        $user = User::firstOrCreate(
            ['email' => $email], 
            [
                'phone' => $formattedPhone, 
                'country_code' => $countryCode,
                'password' => Hash::make('password123'),
                'trust_score' => 100,
                'is_active' => true,
                'branch_id' => $branchId,
                'current_level_id' => $level?->id, 
                'email_verified_at' => now(),
                'avatar_type' => 'icon',
                'avatar_source' => 'avatar_1.svg'
            ]
        );

        UserProfile::updateOrCreate(
            ['user_id' => $user->id], 
            [
                'first_name' => $first,
                'last_name' => $last,
                'birth_date' => '1990-01-01',
                'is_identity_verified' => true, 
            ]
        );

        if (!$user->hasRole($role->name)) {
            $user->assignRole($role->name);
        }
    }
}