<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\HasBinaryUuid; // <--- OBLIGATORIO

class Provider extends Model
{
    use HasFactory, SoftDeletes, HasBinaryUuid;
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'company_name', 'commercial_name', 'tax_id', 'internal_code',
        'contact_name', 'email_orders', 'phone', 'address', 'city',
        'lead_time_days', 'min_order_value', 'credit_days', 'credit_limit',
        'is_active', 'notes'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'min_order_value' => 'decimal:2',
        'credit_limit' => 'decimal:2'
    ];
    public function brands()
    {
        return $this->hasMany(Brand::class);
    }
}