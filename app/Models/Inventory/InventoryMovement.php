<?php

declare(strict_types=1);

namespace App\Models\Inventory;

use App\Traits\HasUv7;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    use HasUv7;

    public $incrementing = false;
    protected $keyType = 'string';
    
    // Desactivar marcas de tiempo estándar: se utiliza únicamente created_at nativo por migración
    public $timestamps = false;

    protected $fillable = [
        'id', 'branch_id', 'sku_id', 'inventory_lot_id', 'admin_id',
        'type', 'quantity', 'balance_after', 'reference', 'reason', 'created_at'
    ];

    protected $casts = [
        'quantity'      => 'float',
        'balance_after' => 'float',
        'created_at'    => 'datetime',
    ];

    /**
     * RECTIFICACIÓN: Blindaje contable de inmutabilidad absoluta sobre transacciones ejecutadas en el Kardex.
     */
    protected static function booted(): void
    {
        static::updating(function () {
            throw new \BadMethodCallException('VIOLACIÓN_DE_PROTOCOLO: Los registros de Kardex son inmutables.');
        });

        static::deleting(function () {
            throw new \BadMethodCallException('VIOLACIÓN_DE_PROTOCOLO: Los registros de Kardex no pueden ser removidos.');
        });
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Operations\Branch::class);
    }

    public function sku(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Catalog\Sku::class);
    }

    public function lot(): BelongsTo
    {
        return $this->belongsTo(InventoryLot::class, 'inventory_lot_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Users\Admin::class);
    }
}