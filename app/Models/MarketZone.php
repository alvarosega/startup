<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes; // Para el borrado lógico
use Illuminate\Database\Eloquent\Concerns\HasUuids; // Para el estándar UUID

class MarketZone extends Model
{
    use HasFactory, SoftDeletes, HasUuids; // Sincronizados

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'slug',
        'hex_color',
        'svg_id',
        'description'
    ];

    public function categories()
    {
        return $this->hasMany(Category::class);
    }
    public static function getAllWithStats()
    {
        return self::withCount('categories')
            ->latest()
            ->get();
    }
}