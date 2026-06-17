<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasUv7;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryLot extends Model
{
    use HasUv7;

    public $incrementing = false;
    protected $keyType = 'string';

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
        'expiration_date'
    ];

    protected $casts = [
        'is_safety_stock'   => 'boolean',
        'expiration_date'   => 'date',
        'quantity'          => 'float', 
        'initial_quantity'  => 'float',
        'reserved_quantity' => 'float'
    ];

    public function purchase(): BelongsTo { return $this->belongsTo(Purchase::class); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
    public function sku(): BelongsTo { return $this->belongsTo(Sku::class); }
    
    public function movements(): HasMany 
    { 
        return $this->hasMany(InventoryMovement::class, 'inventory_lot_id'); 
    }
}