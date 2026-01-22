<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Bundle extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'slug', 'description', 'image_path', 'is_active', 'fixed_price'];

    // Relación con SKUs a través de la tabla pivote
    public function skus(): BelongsToMany
    {
        return $this->belongsToMany(Sku::class, 'bundle_items')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }

    // Sistema de calificaciones
    public function reviews(): MorphMany
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    // Helper para calcular precio dinámico si no tiene precio fijo
    public function getCalculatedPriceAttribute($branchId)
    {
        if ($this->fixed_price) return $this->fixed_price;

        return $this->skus->sum(function($sku) use ($branchId) {
            return $sku->getCurrentPrice($branchId) * $sku->pivot->quantity;
        });
    }
}