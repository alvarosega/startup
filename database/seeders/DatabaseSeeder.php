<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Level;
use App\Models\Branch;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Permisos y Roles (Base fundamental)
        $this->call(RolesAndPermissionsSeeder::class);

        // 2. Datos Maestros
        $this->call([
            LevelSeeder::class,
            ComplianceSeeder::class,
            ProviderSeeder::class,
            BrandSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            InventorySeeder::class
        ]);

        // 3. Crear Sucursal Principal
        $branchLaPaz = Branch::firstOrCreate(
            ['name' => 'Sede Central - Sopocachi'],
            [
                'city' => 'La Paz', 
                'address' => 'Av. 6 de Agosto #2020', 
                'latitude' => -16.5083,
                'longitude' => -68.1272,
                'is_active' => true
            ]
        );

        // 4. Recuperar Roles y Niveles necesarios
        $roles = [
            'super_admin' => Role::where('name', 'super_admin')->first(),
            'branch_admin' => Role::where('name', 'branch_admin')->first(),
            'inventory_manager' => Role::where('name', 'inventory_manager')->first(),
        ];
        
        $levelBronce = Level::where('name', 'Bronce')->first();

        // 5. Crear Usuarios

        // A. Super Admin
        $this->createUser(
            '70000000', 'BO',
            'admin@bolivialogistics.com', 
            'Super', 'Admin', 
            null, // Sin sucursal fija (Global)
            $roles['super_admin'], 
            $levelBronce
        );

        // B. Branch Admin (Para pruebas locales)
        $this->createUser(
            '70000001', 'BO',
            'branch@bolivialogistics.com', 
            'Gerente', 'Sucursal', 
            $branchLaPaz->id, 
            $roles['branch_admin'], 
            $levelBronce
        );

        // C. Inventory Manager (Para pruebas operativas)
        $this->createUser(
            '70000002', 'BO',
            'inventory@bolivialogistics.com', 
            'Jefe', 'Inventario', 
            $branchLaPaz->id, 
            $roles['inventory_manager'], 
            $levelBronce
        );
    }

    /**
     * Helper para crear usuarios completos con perfil y rol.
     */
    private function createUser($phone, $countryCode, $email, $first, $last, $branchId, $role, $level)
    {
        if (!$role) return; // Seguridad si el rol no existe

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
                'current_level_id' => $level ? $level->id : null,
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