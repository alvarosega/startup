<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialIdentity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'provider_name', // 'google', 'apple'
        'provider_id',   // '103284...'
        'data_json',     // Tokens extra
    ];

    // Convierte automáticamente el JSON a Array al leerlo
    protected $casts = [
        'data_json' => 'array',
    ];

    // Pertenece a un Usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}