<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // <--- USAR ESTE

class Provider extends Model
{
    use HasFactory, SoftDeletes, HasUuids; // <--- CAMBIAR TRAIT

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'company_name',
        'commercial_name',
        'tax_id',
        'internal_code',
        'contact_name',
        'email_orders',
        'phone',
        'address',
        'city',
        'lead_time_days',
        'min_order_value',
        'credit_days',
        'credit_limit',
        'is_active',
        'notes'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'lead_time_days' => 'integer',
        'min_order_value' => 'decimal:2',
        'credit_limit' => 'decimal:2',
    ];
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    public function brands()
    {
        return $this->hasMany(Brand::class);
    }
}