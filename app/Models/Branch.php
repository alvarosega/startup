<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 
        'phone',
        'city', 
        'address', 
        'coverage_polygon',
        'opening_hours',
        'is_active',
        // --- AGREGAR ESTOS DOS ---
        'latitude', 
        'longitude'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'coverage_polygon' => 'array',
        'opening_hours' => 'array',
        'latitude' => 'float',  // Recomendado
        'longitude' => 'float'  // Recomendado
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}