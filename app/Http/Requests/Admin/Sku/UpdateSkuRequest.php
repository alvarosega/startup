<?php
namespace App\Http\Requests\Admin\Sku;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Sku;

class UpdateSkuRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        // Resolución segura de UUID contra inyecciones
        $sku = $this->route('sku');
        $skuId = $sku instanceof Sku ? $sku->id : $sku;

        return [
            'name'              => ['required', 'string', 'max:255'],
            'code'              => [
                'nullable', 'string', 
                Rule::unique('skus', 'code')->ignore($skuId)->whereNull('deleted_at')
            ],
            // LA LEY: Consistencia estricta con la base de datos
            'base_price'        => ['required', 'numeric', 'min:0'],
            'conversion_factor' => ['required', 'numeric', 'min:0.001'],
            'weight'            => ['required', 'numeric', 'min:0'],
            'image'             => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_active'         => ['boolean'],
        ];
    }
}