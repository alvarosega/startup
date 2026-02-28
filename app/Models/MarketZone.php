<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany; // Importación necesaria

class MarketZone extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

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

    /**
     * Relación base con categorías.
     */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    /**
     * Alias semántico para "Pasillos".
     * Esto soluciona el error "Call to undefined relationship [aisles]".
     */
    public function aisles(): HasMany
    {
        return $this->categories()->whereNull('parent_id'); // Asumiendo que los pasillos son categorías raíz
    }

    public static function getAllWithStats()
    {
        return self::withCount('categories')
            ->latest()
            ->get();
    }
}