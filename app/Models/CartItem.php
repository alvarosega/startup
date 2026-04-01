<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    use HasUuids;

    protected $fillable = [
        'cart_id', 'sku_id', 'bundle_id', 
        'quantity', 'price_at_addition', 'is_bundle'
    ];

    protected $casts = [
        'is_bundle' => 'boolean',
        'quantity'  => 'integer',
        'price_at_addition' => 'decimal:2',
    ];

    // PROTOCOLO DE ENLACE: Conecta con el Carrito padre
    public function cart(): BelongsTo { return $this->belongsTo(Cart::class); }

    // PROTOCOLO DE ENLACE: Conecta con el SKU (si no es bundle)
    public function sku(): BelongsTo { return $this->belongsTo(Sku::class); }

    // PROTOCOLO DE ENLACE: Conecta con el Bundle (si es bundle)
    public function bundle(): BelongsTo { return $this->belongsTo(Bundle::class); }
}