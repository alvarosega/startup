<?php

namespace App\Http\Requests\Customer\Profiles;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'birth_date' => ['nullable', 'date', 'before:today'],
            'gender'     => ['nullable', 'string', 'in:male,female,other,prefer_not_to_say'],
        ];
    }
}