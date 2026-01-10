<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'birth_date',
        'gender',
        'license_number',
        'vehicle_type',
        'license_plate',
        'is_identity_verified'
    ];
    // Relación inversa: El perfil pertenece al usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Accessor: Nombre Completo virtual
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }
}