<?php

declare(strict_types=1);

namespace Database\Factories\Operations;

use App\Models\Operations\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Operations\Branch>
 */
class BranchFactory extends Factory
{
    protected $model = Branch::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->company() . ' Branch';

        return [
            'id' => (string) Str::uuid7(),
            'name' => $name,
            'slug' => Str::slug($name),
            'city' => 'La Paz',
            'phone' => $this->faker->e164PhoneNumber(),
            'address' => $this->faker->address(),
            
            // Inyección espacial nativa para MySQL (SRID 0 por defecto en la migración)
            'location' => DB::raw("ST_GeomFromText('POINT(-16.500000 -68.150000)')"),
            'coverage_polygon' => DB::raw("ST_GeomFromText('POLYGON((-16.60 -68.20, -16.40 -68.20, -16.40 -68.10, -16.60 -68.10, -16.60 -68.20))')"),
            
            'delivery_base_fee' => 10.00,
            'delivery_price_per_km' => 2.50,
            'surge_multiplier' => 1.00,
            'min_order_amount' => 0.00,
            'small_order_fee' => 0.00,
            'base_service_fee_percentage' => 5.00,
            'is_default' => false,
            'is_active' => true,
            'deleted_epoch' => 0,
        ];
    }
}