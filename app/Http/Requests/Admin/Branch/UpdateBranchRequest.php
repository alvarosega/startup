<?php
namespace App\Http\Requests\Admin\Branch;

use Illuminate\Foundation\Http\FormRequest;
use App\Traits\ValidatesGlobalIdentity;
use Illuminate\Validation\Rule;

class UpdateBranchRequest extends FormRequest
{
    use ValidatesGlobalIdentity;

    public function authorize(): bool
    {
        // La autorización se delega al Middleware/Policy del controlador
        return true; 
    }

    protected function prepareForValidation(): void
    {
        $this->normalizeIdentityData();
    }

    public function rules(): array
    {
        // Obtenemos el ID de la sucursal desde la ruta para las excepciones de 'unique'
        $branchId = $this->route('branch')->id;

        return [
            'name' => [
                'required', 
                'string', 
                'max:255', 
                Rule::unique('branches', 'name')->ignore($branchId)
            ],
            'city'    => ['required', 'string', 'max:100'],
            'phone'   => $this->globalPhoneRules($branchId), // Regla global con excepción de ID actual
            'address' => ['nullable', 'string', 'max:500'],
            'latitude'  => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'coverage_polygon' => ['nullable', 'array'],
            'opening_hours'    => ['nullable', 'array'],
            'is_active'        => ['boolean'],
            'is_default'       => ['boolean'], // Nuevo campo de sistema
            'delivery_base_fee' => ['required', 'numeric', 'min:0'],
            'delivery_price_per_km' => ['required', 'numeric', 'min:0'],
            'surge_multiplier' => ['required', 'numeric', 'min:1'],
            'min_order_amount' => ['required', 'numeric', 'min:0'],
            'small_order_fee' => ['required', 'numeric', 'min:0'],
            'base_service_fee_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            ];
    }
}