<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\HasBinaryUuid; // <--- 1. IMPORTAR

class MarketZone extends Model
{
    use HasFactory, SoftDeletes, HasBinaryUuid; // <--- 2. AÑADIR TRAIT

    protected $fillable = [
        'name', 'slug', 'hex_color', 
        'svg_id', 'description'
    ];

    public function categories()
    {
        return $this->hasMany(Category::class);
    }
}