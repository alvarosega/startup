<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code', 'user_id', 'branch_id', 'status', 
        'reservation_expires_at', 'total_amount', 
        'proof_of_payment', 'delivery_data', 'driver_id'
    ];

    protected $casts = [
        'delivery_data' => 'array',
        'reservation_expires_at' => 'datetime',
        'total_amount' => 'decimal:2'
    ];

    public function items() { return $this->hasMany(OrderItem::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function branch() { return $this->belongsTo(Branch::class); }
}