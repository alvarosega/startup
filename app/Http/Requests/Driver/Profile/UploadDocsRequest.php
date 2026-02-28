<?php

namespace App\Http\Requests\Driver\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UploadDocsRequest extends FormRequest
{
    public function authorize(): bool
    {
        // La autorización real de acceso al silo ya la hace el middleware auth:driver
        return true; 
    }

    public function rules(): array
    {
        return [
            'ci_front'      => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'license_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'vehicle_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'image' => 'El archivo debe ser una imagen válida.',
            'mimes' => 'El formato permitido es JPG, PNG o WEBP.',
            'max'   => 'La imagen no debe superar los 5MB.',
        ];
    }
}