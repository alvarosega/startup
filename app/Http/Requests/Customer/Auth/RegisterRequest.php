<?php

namespace App\Http\Requests\Customer\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;
use App\Traits\ValidatesGlobalIdentity; // <--- AÑADIR

class RegisterRequest extends FormRequest
{
    use ValidatesGlobalIdentity; // <--- USAR

    public function authorize(): bool { return true; }

    protected function prepareForValidation() 
    {
        $this->normalizeIdentityData();
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'], // OBLIGATORIO
            'last_name'  => ['required', 'string', 'max:100'], // OBLIGATORIO
            'phone'      => ['required', 'string', 'unique:customers,phone'],
            'email'      => ['required', 'email', 'unique:customers,email'],
            'password'   => ['required', 'confirmed', 'min:8'],
            'address'    => ['required', 'string'],
            'latitude'   => ['required', 'numeric'],
            'longitude'  => ['required', 'numeric'],
            'branch_id'  => ['nullable', 'string'],
            'avatar_type'   => ['required', 'string', 'in:icon,custom'],
            'avatar_source' => ['nullable', 'string'],
            'avatar_file'   => ['nullable', 'image', 'max:2048'],
        ];
    }
}