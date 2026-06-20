<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Symfony\Component\Uid\Uuid;

class Order extends Model
{


    
    use SoftDeletes, HasUuids;
    public function newUniqueId(): string
    {
        return (string) Uuid::v7();
    }

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'code', 'customer_id', 'branch_id', 'driver_id',
        'delivery_type', 'delivery_data', 'status', 'reservation_expires_at',
        'items_subtotal', 'delivery_fee', 'service_fee', 'total_amount',
        'payment_method', 'proof_of_payment', 'bank_reference',
        'billing_nit', 'billing_name', 'rejection_reason', 'reviewed_at', 'reviewed_by',
        'pickup_otp', 'delivery_otp' // <-- AÑADIDOS PARA FASE 3
    ];
    public function otps(): HasMany { 
        return $this->hasMany(OrderOtp::class); 
    }

    public function getRouteKeyName(): string
    {
        return 'code';
    }
    protected $casts = [
        'delivery_data' => 'array',
        'reservation_expires_at' => 'datetime',
        'reviewed_at' => 'datetime',
        
        // Casteo estricto para cálculos precisos
        'items_subtotal' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'service_fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];
    public function reviewer(): BelongsTo 
    { 
        return $this->belongsTo(SuperAdmin::class, 'reviewed_by'); 
    }

    public function items(): HasMany { return $this->hasMany(OrderItem::class); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function driver(): BelongsTo { return $this->belongsTo(Driver::class); }
}