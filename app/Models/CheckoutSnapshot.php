<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Symfony\Component\Uid\Uuid;

class CheckoutSnapshot extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'cart_id', 'customer_id', 'branch_id', 'logistics_data', 'expires_at'
    ];

    protected $casts = [
        'logistics_data' => 'array',
        'expires_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Uuid::v7();
            }
        });
    }
}