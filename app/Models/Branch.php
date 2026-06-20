<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasUv7;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class Branch extends Model
{
    use HasFactory, SoftDeletes, HasUv7;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name', 'slug', 'phone', 'city', 'address', 
        'coverage_polygon', 'opening_hours', 'is_active', 
        'is_default', 'latitude', 'longitude',
        'delivery_base_fee', 'delivery_price_per_km', 
        'surge_multiplier', 'min_order_amount', 
        'small_order_fee', 'base_service_fee_percentage',
        'deleted_epoch'
    ];

    protected $casts = [
        'is_active'                   => 'boolean',
        'opening_hours'               => 'array',
        'coverage_polygon'            => 'array',
        'is_default'                  => 'boolean',
        'latitude'                    => 'float',
        'longitude'                   => 'float',
        'delivery_base_fee'           => 'float',
        'delivery_price_per_km'       => 'float',
        'surge_multiplier'            => 'float',
        'min_order_amount'            => 'float',
        'small_order_fee'             => 'float',
        'base_service_fee_percentage' => 'float',
        'deleted_epoch'               => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('deleted_epoch', 0);
    }   
    
    protected static function booted(): void
    {
        static::saving(function (self $branch) {
            if ($branch->is_default) {
                static::where('id', '!=', $branch->id)
                      ->where('deleted_epoch', 0)
                      ->update(['is_default' => false]);
                Cache::forget('shop_default_branch_id');
            }
        });

        static::deleting(function (self $model) {
            $model->deleted_epoch = time();
            $model->save();
        });

        static::restoring(function (self $model) {
            $model->deleted_epoch = 0;
        });
    }

    public static function getMinimalList()
    {
        return self::active()->orderBy('name')->get(['id', 'name']);
    }
}