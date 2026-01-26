<?php

namespace App\Models;

use App\Models\Concerns\HasUuidv7;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserAddress extends Model
{
    use HasFactory, SoftDeletes;
    use HasUuidv7;


    protected $fillable = [
        'user_id',
        'branch_id', // CRÍTICO: Vincula la dirección a la sucursal que le da cobertura
        'alias',
        'address',
        'latitude',
        'longitude',
        'reference',
        'is_default'
    ];

    protected $casts = [
        'latitude' => 'float',  // Importante para que Leaflet lo lea bien en JS
        'longitude' => 'float',
        'is_default' => 'boolean',
    ];

    // --- RELACIONES ---

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Esta relación nos permite saber a qué inventario descontar stock
    // cuando el usuario elige esta dirección.
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    // --- EVENTOS DE MODELO ---

    // Lógica: Si marco una dirección como default, las demás dejan de serlo.
    protected static function booted()
    {
        static::saving(function ($address) {
            if ($address->is_default) {
                UserAddress::where('user_id', $address->user_id)
                    ->where('id', '!=', $address->id)
                    ->update(['is_default' => false]);
            }
        });
    }

}