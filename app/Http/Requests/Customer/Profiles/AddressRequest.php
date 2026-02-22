<?php

namespace App\Http\Requests\Customer\Profiles;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class AddressRequest extends FormRequest
{
    /**
     * Solo los clientes autenticados pueden gestionar direcciones.
     */
    public function authorize(): bool
    {
        return Auth::guard('customer')->check();
    }

    /**
     * REGLAS DE ORO: Validación estricta de geolocalización.
     */
    public function rules(): array
    {
        return [
            'alias'     => ['required', 'string', 'max:50'],
            'address'   => ['required', 'string', 'max:255'],
            'details'   => ['nullable', 'string', 'max:255'], // Equivale a 'reference' en DB
            'latitude'  => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'branch_id' => ['nullable', 'string', 'uuid'], // Validamos el UUID String
            'is_default'=> ['boolean'],
        ];
    }

    /**
     * Mensajes personalizados para una UX Premium.
     */
    public function messages(): array
    {
        return [
            'alias.required'    => 'Debes asignar un nombre a tu ubicación (ej: Casa).',
            'address.required'  => 'La dirección física es obligatoria.',
            'latitude.required' => 'Es necesario marcar un punto exacto en el mapa.',
            'branch_id.uuid'    => 'El identificador de sucursal no es válido.',
        ];
    }
}