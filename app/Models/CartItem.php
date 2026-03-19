<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    use HasUuids;

    protected $table = 'cart_items';

    // CORRECCIÓN CRÍTICA: Permitir la asignación de los nuevos campos
    protected $fillable = [
        'cart_id', 
        'sku_id', 
        'bundle_id',         // <--- Añadido
        'quantity',
        'price_at_addition', // <--- Añadido
        'is_bundle'          // <--- Añadido
    ];

    protected $casts = [
        'is_bundle' => 'boolean',
        'price_at_addition' => 'decimal:2',
    ];

    public function sku(): BelongsTo 
    { 
        return $this->belongsTo(Sku::class, 'sku_id'); 
    }

    public function cart(): BelongsTo 
    { 
        return $this->belongsTo(Cart::class, 'cart_id'); 
    }

    // NUEVO: Relación para los combos atómicos
    public function bundle(): BelongsTo
    {
        return $this->belongsTo(Bundle::class, 'bundle_id');
    }
}