<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Symfony\Component\Uid\Uuid;

class IdempotencyKey extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'key', 'customer_id', 'request_path', 'response_code', 'response_body'
    ];

    protected $casts = [
        'response_body' => 'array',
        'response_code' => 'integer',
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