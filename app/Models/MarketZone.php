<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MarketZone extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'slug', 'hex_color', 'svg_id', 'description'];

    // Relación: Una zona tiene muchas categorías
    public function categories()
    {
        return $this->hasMany(Category::class);
    }
}