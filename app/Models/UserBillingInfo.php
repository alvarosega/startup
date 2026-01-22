<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBillingInfo extends Model
{
    use HasFactory;

    protected $table = 'user_billing_infos'; // Especificamos por si acaso

    protected $fillable = [
        'user_id',
        'nit_number',
        'business_name',
        'is_default'
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Lógica: Solo un NIT por defecto a la vez
    protected static function booted()
    {
        static::saving(function ($billing) {
            if ($billing->is_default) {
                UserBillingInfo::where('user_id', $billing->user_id)
                    ->where('id', '!=', $billing->id)
                    ->update(['is_default' => false]);
            }
        });
    }
}