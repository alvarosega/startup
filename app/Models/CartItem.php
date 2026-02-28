<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    use HasUuids;

    protected $table = 'cart_items';

    protected $fillable = [
        'cart_id', 
        'sku_id', 
        'quantity'
    ];

    public function sku(): BelongsTo 
    { 
        return $this->belongsTo(Sku::class, 'sku_id'); 
    }

    public function cart(): BelongsTo 
    { 
        return $this->belongsTo(Cart::class, 'cart_id'); 
    }

}