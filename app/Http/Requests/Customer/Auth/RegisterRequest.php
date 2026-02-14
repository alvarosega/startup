<?php

namespace App\Http\Requests\Customer\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'max:20', 'unique:customers,phone'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:customers,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'terms' => ['accepted'],
            
            // Datos de Dirección
            'alias' => ['nullable', 'string', 'max:50'],
            'address' => ['required', 'string', 'max:255'],
            'details' => ['nullable', 'string', 'max:255'],
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            
            // Branch ID llega como Hex String (32 chars)
            'branch_id' => ['nullable', 'string'], 
            
            // Avatar
            'avatar_type' => ['required', 'in:icon,custom'],
            'avatar_source' => ['nullable', 'string'],
            'avatar_file' => ['nullable', 'image', 'max:3072'],
        ];
    }
}