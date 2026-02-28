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

    // EL ESCUDO DE ASIGNACIÓN MASIVA ACTUALIZADO
    protected $fillable = [
        'code', 'customer_id', 'branch_id', 'driver_id',
        'delivery_type', 'delivery_data', 'status', 'reservation_expires_at',
        
        // Ledger Financiero
        'delivery_fee', 'service_fee', 'total_amount',
        'payment_type', 'advance_amount', 'balance_amount',
        
        // Ledger de Comprobantes Duales
        'advance_proof', 'advance_status', 
        'balance_proof', 'balance_status',
        
        // Auditoría
        'bank_reference', 'rejection_reason', 'reviewed_at'
    ];

    protected $casts = [
        'delivery_data' => 'array',
        'reservation_expires_at' => 'datetime',
        'reviewed_at' => 'datetime',
        
        // Casteo de dinero para evitar errores de frontend
        'delivery_fee' => 'decimal:2',
        'service_fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'advance_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',
    ];

    public function items(): HasMany { return $this->hasMany(OrderItem::class); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
    
    // Aislamiento: Apuntamos a tablas específicas, no a un "User" genérico
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function driver(): BelongsTo { return $this->belongsTo(Driver::class); }
}