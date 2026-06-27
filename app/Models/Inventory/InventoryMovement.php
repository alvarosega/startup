<?php

declare(strict_types=1);

namespace App\Models\Inventory;

use App\Traits\HasUv7;
use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    use HasUv7;

    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id', 'branch_id', 'sku_id', 'inventory_lot_id', 'admin_id',
        'type', 'quantity', 'balance_after', 'reference', 'reason', 'created_at'
    ];

    protected static function booted(): void
    {
        static::updating(function () {
            throw new \BadMethodCallException('VIOLACIÓN_AUDITORIA: El Kardex es estrictamente inmutable.');
        });

        static::deleting(function () {
            throw new \BadMethodCallException('VIOLACIÓN_AUDITORIA: Prohibido remover registros del Kardex.');
        });
    }
}