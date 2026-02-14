<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\HasBinaryUuid; // <--- IMPORTANTE

class CartItem extends Model
{
    use HasBinaryUuid; // <--- APLICAR EL BLINDAJE

    protected $table = 'cart_items';

    protected $fillable = [
        'cart_id', 'product_id', 'quantity', 'price', 'options'
    ];

    protected $casts = [
        'options' => 'array',
        'price' => 'decimal:2',
    ];

    public function cart() { return $this->belongsTo(Cart::class); }
    // Asumo que tienes un modelo Product, si no, comenta esta línea
    public function product() { return $this->belongsTo(Product::class); }

    // BLINDAJE DE FKs
    public function getCartIdAttribute($value)
    {
        if (is_string($value) && strlen($value) === 16) return bin2hex($value);
        return $value;
    }
    
    public function getProductIdAttribute($value)
    {
        if (is_string($value) && strlen($value) === 16) return bin2hex($value);
        return $value;
    }
}