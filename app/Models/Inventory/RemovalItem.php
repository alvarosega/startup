<?php

declare(strict_types=1);

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RemovalItem extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['id', 'removal_request_id', 'inventory_lot_id', 'quantity', 'unit_cost'];

    protected $casts = [
        'quantity' => 'float',
        'unit_cost' => 'float',
    ];

    protected static function booted(): void
    {
        static::updating(function () {
            throw new \BadMethodCallException('VIOLACIÓN_CONTABLE: Las líneas de detalle de una baja aprobada no admiten modificaciones.');
        });

        static::deleting(function () {
            throw new \BadMethodCallException('VIOLACIÓN_CONTABLE: Prohibido remover ítems vinculados a mermas asentadas.');
        });
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(RemovalRequest::class, 'removal_request_id');
    }

    public function lot(): BelongsTo
    {
        return $this->belongsTo(InventoryLot::class, 'inventory_lot_id');
    }
}