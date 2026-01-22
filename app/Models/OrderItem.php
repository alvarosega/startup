<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    public $timestamps = false; // No necesitamos created_at en items fijos

    protected $fillable = [
        'order_id', 'sku_id', 'quantity', 'unit_price', 'subtotal'
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2'
    ];

    public function sku() { return $this->belongsTo(Sku::class); }
}