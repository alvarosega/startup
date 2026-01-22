<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryRemoval extends Model
{
    use HasFactory;

    // Si decides migrar esta tabla también, el nombre sería 'inventory_removals'
    // Asumiremos que mantuviste el nombre de la tabla o lo cambiaste. 
    // Si cambiaste la migración a inglés, Laravel lo detecta solo.

    protected $fillable = [
        'branch_id', 'sku_id', 'user_id', 'approved_by',
        'quantity', 'reason', 'notes', 'evidence_url', // Cambié motivo -> reason, cantidad -> quantity
        'status', 'processed_at'
    ];

    const REASONS = [
        'DAMAGE' => 'Rotura o Daño Físico',
        'EXPIRATION' => 'Vencimiento / Caducidad',
        'THEFT' => 'Robo o Hurto',
        'INTERNAL_USE' => 'Consumo Interno / Marketing',
        'ADJUSTMENT' => 'Error de Inventario / Ajuste',
        'DISASTER' => 'Desastre Natural'
    ];

    public function sku() { return $this->belongsTo(Sku::class); }
    public function branch() { return $this->belongsTo(Branch::class); }
    public function requester() { return $this->belongsTo(User::class, 'user_id'); }
    public function approver() { return $this->belongsTo(User::class, 'approved_by'); }
}