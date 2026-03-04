<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverDetail extends Model
{

    protected $table = 'driver_details';
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
}