<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Provider extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_name', 'commercial_name', 'tax_id', 'internal_code',
        'contact_name', 'email_orders', 'phone', 'address', 'city',
        'lead_time_days', 'min_order_value', 'credit_days', 'credit_limit',
        'is_active', 'notes'
    ];

    protected $casts = ['is_active' => 'boolean'];

    // Relación: Marcas que este proveedor distribuye oficialmente
    public function officialBrands()
    {
        return $this->hasMany(Brand::class, 'provider_id');
    }
}