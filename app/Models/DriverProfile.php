<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverProfile extends Model
{
    use HasFactory, SoftDeletes;

    // Indicamos que la PK no es autoincremental y es el UUID del Driver
    protected $primaryKey = 'driver_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'driver_id',
        'first_name',
        'last_name',
        'license_number',
        'license_plate',
        'vehicle_type',
        'avatar_type',
        'avatar_source',
        'rejection_reason',
        'ci_front_path',
        'license_photo_path',
        'vehicle_photo_path',
    ];

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'driver_id', 'id');
    }
    public function profile(): HasOne
    {
        // IMPORTANTE: El segundo parámetro debe coincidir con el campo en tu migración
        return $this->hasOne(DriverProfile::class, 'driver_id', 'id');
    }
}