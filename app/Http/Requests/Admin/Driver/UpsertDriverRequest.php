<?php

namespace App\Http\Requests\Admin\Driver;

use Illuminate\Foundation\Http\FormRequest;
use App\Traits\ValidatesGlobalIdentity;
use Illuminate\Validation\Rule;

class UpsertDriverRequest extends FormRequest
{
    use ValidatesGlobalIdentity;

    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void 
    {
        $this->normalizeIdentityData();
    }

    public function rules(): array
    {
        $driverId = $this->route('driver'); // Captura el UUID desde la ruta si es Update
        $isUpdate = $driverId !== null;

        return [
            'branch_id'      => ['required', 'uuid', 'exists:branches,id'],
            'first_name'     => ['required', 'string', 'max:100'],
            'last_name'      => ['required', 'string', 'max:100'],
            'password'       => $isUpdate ? ['nullable', 'string', 'min:8'] : ['required', 'string', 'min:8'],
            
            // Validación Cruzada Zero-Trust
            'phone' => [
                'required', 
                'string', 
                'max:20',
                Rule::unique('drivers', 'phone')->ignore($driverId),
                Rule::unique('admins', 'phone'),
                Rule::unique('customers', 'phone'),
            ],
            // Si el Admin le puede cambiar/asignar el correo:
            'email' => [
                'required', 
                'email', 
                'max:255',
                Rule::unique('drivers', 'email')->ignore($driverId),
                Rule::unique('admins', 'email'),
                Rule::unique('customers', 'email'),
            ],

            'license_number' => ['required', 'string', 'max:50'],
            'license_plate'  => ['required', 'string', 'max:20'],
            'vehicle_type'   => ['required', 'string', 'in:moto,car,truck'],
            
            // Controles de estado que maneja el Admin
            'is_identity_verified' => ['nullable', 'boolean'],
            'is_active'            => ['nullable', 'boolean'],
            'rejection_reason'     => ['nullable', 'string'],
        ];
    }
}