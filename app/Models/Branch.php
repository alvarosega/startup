<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Facades\Cache;

class Branch extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = [
        'id', 
        'name', 
        'slug', // <-- CRÍTICO: Añadido para que el seeder funcione
        'phone', 
        'city', 
        'address', 
        'coverage_polygon', 
        'opening_hours', 
        'is_active', 
        'is_default', 
        'latitude', 
        'longitude',
        'delivery_base_fee', 
        'delivery_price_per_km', 
        'surge_multiplier',
        'min_order_amount', 
        'small_order_fee', 
        'base_service_fee_percentage'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'opening_hours' => 'array',
        'coverage_polygon' => 'array',
        'is_default' => 'boolean',
        'latitude' => 'float',
        'longitude' => 'float',
        'delivery_base_fee' => 'float',
        'delivery_price_per_km' => 'float',
        'surge_multiplier' => 'float',
        'min_order_amount' => 'float',
        'small_order_fee' => 'float',
        'base_service_fee_percentage' => 'float',
    ];

    /**
     * Scope para filtrar sucursales activas.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }   
    
    /**
     * Lógica de arranque del modelo.
     */
    protected static function booted()
    {
        static::saving(function ($branch) {
            // Garantizar unicidad de la sucursal por defecto
            if ($branch->is_default) {
                // El uso de update() aquí es seguro porque no dispara eventos de Eloquent
                static::where('id', '!=', $branch->id)->update(['is_default' => false]);
                Cache::forget('shop_default_branch_id');
            }
        });
    }

    /**
     * Obtiene una lista simplificada para selectores de interfaz.
     */
    public static function getMinimalList()
    {
        return self::orderBy('name')->get(['id', 'name']);
    }
}