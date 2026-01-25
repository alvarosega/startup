<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    use HasFactory;

    protected $primaryKey = 'user_id';
    public $incrementing = false; 

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'birth_date',
        'gender',
        'is_identity_verified', // Útil si necesitamos actualizar esto, aunque suele ser por sistema
        'ci_front_path',
        'ci_back_path'
    ];

    protected $casts = [
        'birth_date' => 'date',
        'is_identity_verified' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}