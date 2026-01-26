<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\HasGeospatial;

class Branch extends Model
{
    use SoftDeletes, HasGeospatial;
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
    public static function findCoveringBranch(float $lat, float $lng): ?self
    {
        // 1. Traemos solo sucursales activas que tengan polígono definido
        $branches = self::where('is_active', true)
            ->whereNotNull('coverage_polygon')
            ->get();

        // 2. Iteramos en PHP (es rápido para < 100 sucursales)
        foreach ($branches as $branch) {
            if ($branch->isPointInPolygon($lat, $lng, $branch->coverage_polygon)) {
                return $branch;
            }
        }

        return null;
    }
}