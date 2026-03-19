<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\{BelongsToMany, BelongsTo, MorphMany};
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Bundle extends Model
{
    use SoftDeletes, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'branch_id', 'name', 'slug', 'description', 
        'image_path', 'is_active', 'fixed_price', 
        'is_editable', 'starts_at', 'ends_at'
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'is_editable' => 'boolean', // Añadido para consistencia en Vue
        'fixed_price' => 'decimal:2',
        'starts_at'   => 'datetime', 
        'ends_at'     => 'datetime',
    ];

    // RELACIÓN ÚNICA Y MAESTRA
    public function skus(): BelongsToMany
    {
        return $this->belongsToMany(Sku::class, 'bundle_items')
                    ->using(BundleItem::class) // Asegúrate de que el modelo BundleItem exista
                    ->withPivot('quantity') 
                    ->withTimestamps();
    }

    // ELIMINAR EL MÉTODO items() PARA EVITAR CONFUSIÓN

    public function branch(): BelongsTo 
    { 
        return $this->belongsTo(Branch::class); 
    }

    // Scope para facilitar filtrado en controladores
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }
}