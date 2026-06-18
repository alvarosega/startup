<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    use HasUuids;

    protected $table = 'cart_items';

    // RECTIFICACIÓN: Remoción absoluta de bundle_id para consolidar la entrada única vía sku_id
    protected $fillable = [
        'cart_id', 
        'sku_id', 
        'quantity', 
        'price_at_addition', 
        'is_bundle'
    ];

    protected $casts = [
        'is_bundle'         => 'boolean',
        'quantity'          => 'integer',
        'price_at_addition' => 'decimal:2',
    ];

    /**
     * Enlace inverso hacia el contenedor principal.
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Punto de entrada único para la resolución comercial de datos, imágenes y stock.
     * Si 'is_bundle' es verdadero, este SKU apunta directamente a la oferta unificada.
     */
    public function sku(): BelongsTo
    {
        return $this->belongsTo(Sku::class);
    }
}