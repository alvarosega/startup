<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation()
    {
        if ($this->has('phone')) {
            $phone = str_replace([' ', '-', '(', ')'], '', $this->phone);
            if (!str_starts_with($phone, '+')) {
                $phone = '+591' . $phone;
            }
            $this->merge(['phone' => $phone]);
        }
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'regex:/^\+[0-9]{8,15}$/', 'unique:users,phone'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'], // <--- NUEVO
            'password' => ['required', 'confirmed', 'min:8'],
            'role' => ['required', 'in:client,driver'],
            'terms' => ['accepted'],
            
            // Avatar
            'avatar_type' => ['required', 'in:icon,custom'],
            'avatar_source' => ['nullable', 'string'],
            'avatar_file' => ['nullable', 'image', 'max:2048'],
            
            // Ubicación (Solo requerida si es cliente, pero validamos formato si viene)
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'branch_id' => ['nullable', 'exists:branches,id'], // Validar que la sucursal exista
            'alias' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'details' => ['nullable', 'string', 'max:255'],
        ];
    }
    
    // ... tus mensajes personalizados ...
}