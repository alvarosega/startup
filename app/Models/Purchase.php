<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'branch_id', 
        'provider_id', 
        'admin_id', // Ajustado al silo correcto
        'document_number', 
        'purchase_date', 
        'payment_type', 
        'payment_due_date', 
        'total_amount', 
        'notes', 
        'status'
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'payment_due_date' => 'date',
        'total_amount' => 'decimal:2'
    ];

    // Relaciones con tipos de retorno estrictos
    public function provider(): BelongsTo { return $this->belongsTo(Provider::class); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
    public function admin(): BelongsTo { return $this->belongsTo(Admin::class, 'admin_id'); }
    public function inventoryLots(): HasMany { return $this->hasMany(InventoryLot::class); }
}