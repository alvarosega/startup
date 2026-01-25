<?php

namespace App\Http\Requests\Identity;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAvatarRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'avatar_type' => ['required', 'in:custom,icon'],
            // Si es custom, file es obligatorio. Si es icon, source es obligatorio.
            'avatar_file' => ['required_if:avatar_type,custom', 'nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:10240'],
            'avatar_source' => ['required_if:avatar_type,icon', 'nullable', 'string'],
        ];
    }
}