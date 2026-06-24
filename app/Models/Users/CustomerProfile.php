<?php

declare(strict_types=1);

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\Users\AvatarType;

class CustomerProfile extends Model
{
    protected $table = 'customer_profiles';

    // Llave primaria no autoincremental de tipo estricto debido al silo 1:1
    protected $primaryKey = 'customer_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'customer_id',
        'first_name',
        'last_name',
        'birth_date',
        'gender',
        'avatar_type',
        'avatar_source',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'avatar_type' => AvatarType::class, // Cast estructural al Backed Enum
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }
}