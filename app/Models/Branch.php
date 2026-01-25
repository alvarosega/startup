<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'phone', 'city', 'address', 
        'coverage_polygon', 'opening_hours', 
        'is_active', 'latitude', 'longitude'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'coverage_polygon' => 'array', // Auto-conversión JSON <-> Array
        'opening_hours' => 'array',
        'latitude' => 'float',
        'longitude' => 'float'
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}