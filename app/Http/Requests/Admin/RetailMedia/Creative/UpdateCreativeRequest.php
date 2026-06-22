<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\RetailMedia\Creative;

class UpdateCreativeRequest extends StoreCreativeRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        // En edición las imágenes son opcionales para permitir cambios de metadatos sin re-subir archivos
        $rules['image_mobile'] = ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:3072'];
        return $rules;
    }
}