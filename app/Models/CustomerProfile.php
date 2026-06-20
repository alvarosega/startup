<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerProfile extends Model
{
    use HasFactory;

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

    // No ocultamos el customer_id para que Vue sepa a quién pertenece el perfil
    protected $hidden = []; 

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }
}