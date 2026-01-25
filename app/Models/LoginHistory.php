<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginHistory extends Model
{
    // No usamos UUID aquí porque es una tabla de logs (append-only), 
    // pero el user_id SÍ es UUID.
    protected $table = 'login_history';
    public $timestamps = false; // Solo usamos login_at

    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'device_fingerprint',
        'login_at'
    ];

    protected $casts = [
        'login_at' => 'datetime',
    ];
}