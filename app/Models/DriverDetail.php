<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class DriverDetail extends Model
{
    protected $fillable = ['driver_id', 'first_name', 'last_name', 'license_number', 'license_plate', 'vehicle_type'];

    /**
     * También debemos proteger el FK binario
     */
    protected function driverId(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (is_null($value)) return null;
                return (strlen($value) === 16) ? bin2hex($value) : $value;
            },
            set: function ($value) {
                if (is_null($value)) return null;
                return (is_string($value) && strlen($value) === 32) ? hex2bin($value) : $value;
            }
        );
    }
}