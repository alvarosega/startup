<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // <--- NUEVO

class CustomerAddress extends Model
{
    use HasFactory, SoftDeletes, HasUuids; // <--- USAR HasUuids

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'customer_id', 'branch_id', 'alias', 'address', 
        'latitude', 'longitude', 'reference', 'is_default'
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'is_default' => 'boolean',
    ];

    // Dejamos las llaves visibles para la lógica del frontend (Carrito/Checkout)
    protected $hidden = ['deleted_at'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}