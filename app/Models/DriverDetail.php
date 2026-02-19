<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverDetail extends Model
{
    protected $table = 'driver_details';
    protected $primaryKey = 'driver_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'driver_id', 'first_name', 'last_name', 
        'license_number', 'license_plate', 'vehicle_type',
        'avatar_type', 'avatar_source'
    ];

    // BLOQUEO DE SEGURIDAD: Evita que el ID se trate como binario
    public function getDriverIdAttribute($value) {
        return (string) $value;
    }
}