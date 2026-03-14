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
        'id', 'order_id', 'sku_id', 
        
        // Snapshots Históricos (Crucial para integridad)
        'product_name', 'sku_name', 'image_snapshot',
        
        'quantity', 'unit_price', 'subtotal'
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'quantity' => 'integer'
    ];

    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function sku(): BelongsTo { return $this->belongsTo(Sku::class); }
}