<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverLocationLog extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';
    
    // Desactivar updated_at (Append-only table)
    const UPDATED_AT = null;

    protected $fillable = [
        'id',
        'driver_id',
        'order_id',
        'latitude',
        'longitude',
        'created_at'
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'created_at' => 'datetime',
    ];

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'driver_id', 'id');
    }
}