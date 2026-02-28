<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasUuids;

    public $timestamps = false;
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'order_id', 'sku_id', 'quantity', 'unit_price', 'subtotal'
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2'
    ];

    public function order(): BelongsTo 
    { 
        return $this->belongsTo(Order::class); 
    }

    public function sku(): BelongsTo 
    { 
        return $this->belongsTo(Sku::class); 
    }
}