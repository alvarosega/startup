<?php

declare(strict_types=1);

namespace Database\Factories\Users;

use App\Models\Users\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Users\Admin>
 */
class AdminFactory extends Factory
{
    protected $model = Admin::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid7(),
            // CORRECCIÓN: Se remueve 'name' y se adapta al esquema fillable real del modelo
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'phone' => $this->faker->e164PhoneNumber(),
            'email' => $this->faker->unique()->companyEmail(),
            'password' => Hash::make('AdminSecret123*'),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}