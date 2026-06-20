<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'branch_id',
        'reviewer_id',
        'front_ci_path',
        'back_ci_path',
        'selfie_path',
        'status',
        'rejection_reason_id'
    ];
    public function branch() {
        return $this->belongsTo(Branch::class);
    }
    // Quien solicita
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Quien aprueba (Admin)
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    // Por qué se rechazó
    public function rejectionReason()
    {
        return $this->belongsTo(RejectionReason::class);
    }
}