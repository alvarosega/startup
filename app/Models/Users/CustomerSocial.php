<?php

declare(strict_types=1);

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerSocial extends Model
{
    protected $table = 'customer_socials';

    protected $fillable = [
        'customer_id',
        'provider_name',
        'provider_id',
        'data_json',
    ];

    protected $casts = [
        'data_json' => 'array', // Deserialización nativa a estructuras de datos PHP
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }
}