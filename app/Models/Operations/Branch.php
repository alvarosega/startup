<?php

declare(strict_types=1);

namespace App\Models\Operations;

use App\Exceptions\Customer\Auth\ImmutableUuidException;
use App\Models\Users\Admin;
use App\Models\Users\Customer;
use App\Models\Users\Driver;
use App\Traits\HasUv7;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use HasFactory, SoftDeletes, HasUv7;

    protected $table = 'branches';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'slug',
        'city',
        'phone',
        'address',
        'location',
        'coverage_polygon',
        'delivery_base_fee',
        'delivery_price_per_km',
        'surge_multiplier',
        'min_order_amount',
        'small_order_fee',
        'base_service_fee_percentage',
        'is_default',
        'is_active',
        'is_operational_paused',
        'deleted_epoch',
    ];

    protected $hidden = [
        'location',
        'coverage_polygon',
    ];

    protected $casts = [
        'is_default'                  => 'boolean',
        'is_active'                   => 'boolean',
        'is_operational_paused'       => 'boolean',
        'deleted_epoch'               => 'integer',
        'delivery_base_fee'           => 'decimal:2',
        'delivery_price_per_km'       => 'decimal:2',
        'surge_multiplier'            => 'decimal:2',
        'min_order_amount'            => 'decimal:2',
        'small_order_fee'             => 'decimal:2',
        'base_service_fee_percentage' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::updating(function (Branch $branch) {
            if ($branch->isDirty('id')) {
                throw new ImmutableUuidException('id');
            }
        });

        static::deleting(function (Branch $branch) {
            $branch->deleted_epoch = time();
            $branch->saveQuietly();
        });

        static::restoring(function (Branch $branch) {
            $branch->deleted_epoch = 0;
        });
    }

    public function admins(): HasMany
    {
        return $this->hasMany(Admin::class, 'branch_id');
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'branch_id');
    }

    public function drivers(): HasMany
    {
        return $this->hasMany(Driver::class, 'branch_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(BranchSchedule::class, 'branch_id');
    }

    public function dailyCashes(): HasMany
    {
        return $this->hasMany(BranchDailyCash::class, 'branch_id');
    }

    public function providers(): BelongsToMany
    {
        return $this->belongsToMany(Provider::class, 'branch_providers', 'branch_id', 'provider_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->where('deleted_epoch', 0);
    }

    public function scopeWithinCoverage(Builder $query, float $latitude, float $longitude): Builder
    {
        return $query->whereRaw(
            "ST_Contains(coverage_polygon, ST_GeomFromText(?, 4326, 'axis-order=long-lat'))",
            ["POINT({$longitude} {$latitude})"]
        );
    }
}