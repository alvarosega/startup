<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    use HasUuids;

    protected $table = 'cart_items';

    /**
     * Campos asignables.
     * Eliminamos 'product_id' porque ahora usamos 'sku_id' como pivote técnico.
     */
    protected $fillable = [
        'cart_id', 
        'sku_id', 
        'quantity'
    ];

    // =================================================================================
    // RELACIONES (DEFINICIÓN ESTRICTA)
    // =================================================================================

    /**
     * El SKU al que pertenece este ítem. 
     * Esta es la relación que Ziggy y Eloquent no encontraban.
     */
    public function sku(): BelongsTo 
    { 
        return $this->belongsTo(Sku::class, 'sku_id'); 
    }

    /**
     * La cesta de compras padre.
     */
    public function cart(): BelongsTo 
    { 
        return $this->belongsTo(Cart::class, 'cart_id'); 
    }

    // =================================================================================
    // ELIMINACIÓN DE ACCESSORS BINARIOS
    // =================================================================================
    // Se borraron getCartIdAttribute y getProductIdAttribute. 
    // Al usar HasUuids, Eloquent maneja los IDs como strings nativos automáticamente.
}