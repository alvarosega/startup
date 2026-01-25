<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DriverProfile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'license_number',
        'license_plate',
        'vehicle_type', // 'moto', 'car', 'truck'
        'status',       // 'pending', 'verified', 'rejected'
        'rejection_reason',
        'ci_front_path',
        'license_photo_path',
        'vehicle_photo_path',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    // En User.php
    public function driverProfile() // <--- OJO CON EL CAMELCASE
    {
        // Asegúrate de que apunte a la clase correcta y la llave foránea sea correcta
        return $this->hasOne(DriverProfile::class, 'user_id'); 
    }

    public function profile()
    {
        return $this->hasOne(UserProfile::class, 'user_id');
    }
}