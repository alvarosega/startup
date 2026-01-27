<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\HasUuidv7;

class Order extends Model
{
    use SoftDeletes,HasUuidv7;
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'code', 'user_id', 'branch_id','delivery_type', 'status', 
        'reservation_expires_at', 'total_amount', 
        'proof_of_payment', 'rejection_reason', 
        'delivery_data', 'driver_id', 'reviewed_at'
    ];

    protected $casts = [
        'delivery_data' => 'array', // Automáticamente convierte JSON a Array
        'reservation_expires_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    // Relaciones
    public function items() { return $this->hasMany(OrderItem::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function branch() { return $this->belongsTo(Branch::class); }
    public function driver() { return $this->belongsTo(User::class, 'driver_id'); }
}