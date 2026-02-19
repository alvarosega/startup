<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // <--- IMPRESCINDIBLE

class Branch extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'name', 'phone', 'city', 'address', 
        'coverage_polygon', 'opening_hours', 'is_active', 'is_default', 'latitude', 'longitude'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'opening_hours' => 'array',
        'coverage_polygon' => 'array',
        'is_default' => 'boolean',
    ];

               
}