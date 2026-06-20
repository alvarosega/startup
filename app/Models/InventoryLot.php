<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryLot extends Model
{
    use HasUuids;

    protected $table = 'inventory_lots';

    protected $fillable = [
        'purchase_id',
        'transfer_id',
        'branch_id',
        'sku_id',
        'lot_code',
        'quantity',
        'initial_quantity',
        'reserved_quantity',
        'is_safety_stock',
        'is_quarantine',
        'expiration_date',
    ];

    protected $casts = [
        'quantity'          => 'decimal:3',
        'initial_quantity'  => 'decimal:3',
        'reserved_quantity' => 'decimal:3',
        'is_safety_stock'   => 'boolean',
        'is_quarantine'     => 'boolean',
        'expiration_date'   => 'date', // LEY: Mutación mandatoria para soporte de métodos Carbon en controlador
    ];

    public function sku(): BelongsTo
    {
        return $this->belongsTo(Sku::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}