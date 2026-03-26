<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUv7; // Estandarización a UUIDv7
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryLot extends Model
{
    use HasUv7;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'purchase_id',
        'transfer_id', // Añadido para trazabilidad de transferencias
        'branch_id',
        'sku_id',
        'lot_code',
        'quantity',
        'initial_quantity',
        'reserved_quantity', 
        'is_safety_stock',  
        'unit_cost', 
        'expiration_date'
    ];

    protected $casts = [
        'is_safety_stock' => 'boolean',
        'expiration_date' => 'date',
        'unit_cost' => 'decimal:2',
        'quantity' => 'integer',
        'reserved_quantity' => 'integer'
    ];

    public function purchase(): BelongsTo { return $this->belongsTo(Purchase::class); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
    public function sku(): BelongsTo { return $this->belongsTo(Sku::class); }
}