<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasUuids;

    protected $table = 'reviews';

    // Regla 1.C: Identificadores puros
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'customer_id', 
        'product_id', 
        'rating', 
        'comment', 
        'is_verified_purchase'
    ];

    public function customer(): BelongsTo 
    { 
        return $this->belongsTo(Customer::class); 
    }

    public function product(): BelongsTo 
    { 
        return $this->belongsTo(Product::class); 
    }
}