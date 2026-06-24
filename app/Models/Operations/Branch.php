<?php

declare(strict_types=1);

namespace App\Models\Operations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\HasUv7;
use App\Models\Admin;
use App\Models\Users\Customer;
use App\Models\Users\Driver;

class Branch extends Model
{
    use HasUv7, SoftDeletes, HasFactory;

    protected $table = 'branches';

    protected $fillable = [
        'name', 'slug', 'city', 'phone', 'address', 'location', 'coverage_polygon',
        'delivery_base_fee', 'delivery_price_per_km', 'surge_multiplier',
        'min_order_amount', 'small_order_fee', 'base_service_fee_percentage',
        'is_default', 'is_active', 'deleted_epoch'
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'deleted_epoch' => 'integer',
        'delivery_base_fee' => 'decimal:2',
        'delivery_price_per_km' => 'decimal:2',
        'surge_multiplier' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'small_order_fee' => 'decimal:2',
        'base_service_fee_percentage' => 'decimal:2',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::deleting(function (Branch $branch) {
            $branch->deleted_epoch = time();
            $branch->saveQuietly();
        });
    }

    // =================================================================================
    // RELACIONES
    // =================================================================================
    public function admins(): HasMany
    {
        return $this->hasMany(Admin::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function drivers(): HasMany
    {
        return $this->hasMany(Driver::class);
    }

    // =================================================================================
    // LOCAL SCOPES (ABSTRACCIÓN GEOSPACIAL)
    // =================================================================================
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Evalúa qué sucursales cubren un punto geográfico específico mediante la base de datos.
     */
    public function scopeWithinCoverage(Builder $query, float $latitude, float $longitude): Builder
    {
        return $query->whereRaw(
            "ST_Contains(coverage_polygon, ST_GeomFromText(?))",
            ["POINT({$longitude} {$latitude})"]
        );
    }
}