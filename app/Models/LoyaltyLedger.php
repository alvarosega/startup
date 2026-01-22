<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoyaltyLedger extends Model
{
    use HasFactory;

    // Definimos explícitamente la tabla porque Laravel buscaría 'loyalty_ledgers' (plural)
    // y a veces es mejor ser explícito:
    protected $table = 'loyalty_ledger';

    protected $fillable = [
        'user_id',
        'amount',
        'type',      // 'purchase', 'expiration'
        'expires_at'
    ];

    // Casting de fechas para que Carbon funcione (poder usar ->format('d/m/Y'))
    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}