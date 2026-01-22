<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // Recomendado añadir
use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 
        'origin_branch_id', 
        'destination_branch_id', 
        'created_by', 
        'received_by', 
        'status', 
        'notes', 
        'shipped_at', 
        'received_at'
    ];

    // IMPORTANTE: Esto convierte las fechas y strings automáticamente
    protected $casts = [
        'shipped_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    // --- RELACIONES ---

    // Detalles de la transferencia
    public function items() 
    { 
        return $this->hasMany(TransferItem::class); 
    }

    // De dónde sale
    public function origin() 
    { 
        return $this->belongsTo(Branch::class, 'origin_branch_id'); 
    }

    // A dónde llega
    public function destination() 
    { 
        return $this->belongsTo(Branch::class, 'destination_branch_id'); 
    }

    // Quién la creó (Envió)
    public function sender() 
    { 
        return $this->belongsTo(User::class, 'created_by'); 
    }

    // Quién la recibió (Faltaba esta)
    public function receiver() 
    { 
        return $this->belongsTo(User::class, 'received_by'); 
    }
}