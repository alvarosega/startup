<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cart extends Model
{
    use HasUuids, SoftDeletes; // LEY: Sincronización mandatoria con el estado softDeletes de la migración

    protected $table = 'carts';

    protected $fillable = [
        'session_id', 
        'customer_id', 
        'branch_id'
    ];

    protected $casts = [
        'customer_id' => 'string',
        'branch_id'   => 'string',
    ];

    /**
     * Relación con las líneas de artículos contenidas en la bandeja.
     */
    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Vinculación con la sucursal física que gobierna el inventario y precios de este carrito.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Vinculación con la cuenta de cliente (nulo si el flujo opera en modo invitado).
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}