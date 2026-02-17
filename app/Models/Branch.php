<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\HasBinaryUuid; // <--- 1. IMPORTANTE

class Branch extends Model
{
    use HasFactory, SoftDeletes, HasBinaryUuid; // <--- 2. IMPORTANTE (Faltaba esto)

    protected $table = 'branches';

    protected $fillable = [
        'name', 'phone', 'city', 'address', 
        'coverage_polygon', 'opening_hours', 
        'is_active', 'latitude', 'longitude'
    ];
    protected $casts = [
        'is_active' => 'boolean',
        'opening_hours' => 'array',
        'latitude' => 'float',
        'longitude' => 'float',
        
        // CORRECCIÓN: Descomenta o agrega esta línea para que el Array se guarde como JSON
        'coverage_polygon' => 'array', 
    ];

    // PROTECCIÓN CRÍTICA PARA EL JSON
    public function getCoveragePolygonAttribute($value)
    {
        if (is_string($value) && !ctype_print($value)) return null; 
        return $value;
    }
    public function toArray()
    {
        $array = parent::toArray();
        // Convertir el ID binario de la sucursal a Hexadecimal para Vue
        if ($this->getRawOriginal('id')) {
            $array['id'] = bin2hex($this->getRawOriginal('id'));
        }
        return $array;
    }
    public function admins() { return $this->hasMany(Admin::class); }
    public function customers() { return $this->hasMany(Customer::class); }
    // AÑADIR/ACTUALIZAR la propiedad hidden:
    protected $hidden = [
        'id', 
        'deleted_at'
    ];
}