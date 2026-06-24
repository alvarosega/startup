<?php

declare(strict_types=1);

namespace Database\Factories\Users;

use App\Models\Users\Customer;
use App\Models\Operations\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Users\Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid7(),
            'branch_id' => Branch::factory(),
            'phone' => $this->faker->unique()->e164PhoneNumber(),
            'country_code' => 'BO',
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('Secret123*'),
            'idempotency_key' => null,
            'trust_score' => 50,
            'is_active' => true,
            'email_verified_at' => now(),
            
            // Punto espacial requerido por la restricción NOT NULL latente
            'last_known_location' => DB::raw("ST_GeomFromText('POINT(-16.500000 -68.150000)')"),
            
            'last_seen_at' => now(),
            'last_login_at' => now(),
            'was_previously_deleted' => false,
            'needs_password_change' => false,
            'deleted_epoch' => 0,
        ];
    }
}