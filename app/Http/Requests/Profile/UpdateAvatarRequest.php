<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAvatarRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'avatar_type'   => ['required', 'in:icon,custom'],
            'avatar_source' => ['nullable', 'required_if:avatar_type,icon', 'string'],
            'avatar_file'   => ['nullable', 'required_if:avatar_type,custom', 'image', 'max:10240'], // 10MB
        ];
    }
}