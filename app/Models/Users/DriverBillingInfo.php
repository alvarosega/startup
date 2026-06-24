<?php

declare(strict_types=1);

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverBillingInfo extends Model
{
    protected $table = 'driver_billing_infos';

    protected $fillable = [
        'driver_id',
        'nit_number',
        'business_name',
    ];

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'driver_id', 'id');
    }
}