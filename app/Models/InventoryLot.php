<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryLot extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'purchase_id',
        'branch_id',
        'sku_id',
        'lot_code',
        'quantity',
        'initial_quantity',
        'unit_cost',
        'expiration_date'
    ];

    protected $casts = [
        'expiration_date' => 'date',
        'unit_cost' => 'decimal:2',
        'quantity' => 'integer',
        'reserved_quantity' => 'integer'
    ];

    public function purchase(): BelongsTo { return $this->belongsTo(Purchase::class); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
    public function sku(): BelongsTo { return $this->belongsTo(Sku::class); }
}