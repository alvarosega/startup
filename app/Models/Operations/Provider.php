<?php

declare(strict_types=1);

namespace App\Models\Operations;

use App\Traits\HasUv7;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Provider extends Model
{
    use HasFactory, SoftDeletes, HasUv7;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'company_name',
        'commercial_name',
        'slug',
        'tax_id',
        'internal_code',
        'contact_name',
        'email_orders',
        'phone',
        'address',
        'city',
        'lead_time_days',
        'min_order_value',
        'credit_days',
        'credit_limit',
        'is_active',
        'notes',
        'version',
        'deleted_epoch'
    ];

    protected $casts = [
        'is_active'       => 'boolean',
        'lead_time_days'  => 'integer',
        'min_order_value' => 'float',
        'credit_days'     => 'integer',
        'credit_limit'    => 'float',
        'version'         => 'integer',
        'deleted_epoch'   => 'integer',
    ];

    protected static function booted(): void
    {
        static::updating(function (self $model) {
            $model->version++;
        });

        static::deleting(function (self $model) {
            $model->deleted_epoch = time();
            $model->save();
        });

        static::restoring(function (self $model) {
            $model->deleted_epoch = 0;
        });
    }

    public function brands(): HasMany
    {
        return $this->hasMany(\App\Models\Catalog\Brand::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}