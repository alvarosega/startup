<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'min_points', 'color_hex', 'benefits_json'];

    // Convierte el JSON a Array automáticamente al leerlo de DB
    protected $casts = [
        'benefits_json' => 'array',
    ];
}