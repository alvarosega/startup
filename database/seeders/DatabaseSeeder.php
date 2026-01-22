<?php

namespace Database\Seeders;

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Level;
use App\Models\Branch; // <--- ESTO FALTABA
use Spatie\Permission\Models\Role; // Usamos el modelo de Spatie
use Illuminate\Support\Facades\Hash;
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        $this->call([
            LevelSeeder::class,
            ComplianceSeeder::class,
            
            // LOGÍSTICA
            ProviderSeeder::class,
            BrandSeeder::class,
            CategorySeeder::class,
            
            // CATÁLOGO
            ProductSeeder::class,
            
            // OPERACIÓN
            InventorySeeder::class
        ]);

        // ... (Keep your Branch creation logic) ...
        $branchLaPaz = Branch::firstOrCreate(
            ['name' => 'Sede Central - Sopocachi'],
            ['city' => 'La Paz', 'address' => 'Av. 6 de Agosto #2020', 'is_active' => true]
        );

        // ... (Keep your User creation logic) ...
        
        // Retrieve roles created by RolesAndPermissionsSeeder
        $roleSuperAdmin = Role::where('name', 'super_admin')->first();
        $levelBronce = Level::where('name', 'Bronce')->first();

        $this->createUser(
            '70000000', 'BO',
            'admin@bolivialogistics.com', 
            'Super', 'Admin', 
            null, 
            $roleSuperAdmin, 
            $levelBronce
        );
    }

    // ... (Keep your createUser method) ...
    private function createUser($phone, $countryCode, $email, $first, $last, $branchId, $role, $level)
    {
        // ... (Keep existing implementation) ...
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

        // Use assignRole from Spatie
        if (!$user->hasRole($role->name)) {
            $user->assignRole($role->name);
        }
    }
}