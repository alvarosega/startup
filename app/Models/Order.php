<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class Order extends Model
{
    use SoftDeletes, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'code', 'customer_id', 'branch_id', 'driver_id',
        'delivery_type', 'delivery_data', 'status', 'pickup_otp', 'delivery_otp', 
        'reservation_expires_at',
        
        // Ledger Financiero (CRÍTICO: Añadido items_subtotal)
        'items_subtotal', 'delivery_fee', 'service_fee', 'total_amount',
        
        // Pago y Facturación
        'payment_method', 'proof_of_payment', 'bank_reference',
        'billing_nit', 'billing_name',
                
        // Auditoría
        'rejection_reason', 'reviewed_at'
    ];

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

    public function items(): HasMany { return $this->hasMany(OrderItem::class); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function driver(): BelongsTo { return $this->belongsTo(Driver::class); }
}