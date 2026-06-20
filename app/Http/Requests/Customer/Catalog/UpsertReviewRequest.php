<?php

namespace App\Http\Requests\Customer\Catalog;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpsertReviewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // La autorización real ya la hace el middleware auth:customer, 
        // pero por semántica estricta confirmamos que sea el guard correcto.
        return Auth::guard('customer')->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'rating'  => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Mensajes crudos y directos.
     */
    public function messages(): array
    {
        return [
            'rating.required' => 'La calificación es obligatoria.',
            'rating.min'      => 'La calificación mínima es 1.',
            'rating.max'      => 'La calificación máxima es 5.',
            'comment.max'     => 'El comentario no puede exceder los 500 caracteres.',
        ];
    }
}