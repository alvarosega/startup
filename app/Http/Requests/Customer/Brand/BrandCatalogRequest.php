<?php

declare(strict_types=1);

namespace App\Http\Requests\Customer\Brand;

use Illuminate\Foundation\Http\FormRequest;

class BrandCatalogRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:50'],
            'sort'   => ['nullable', 'string', 'in:relevance,price_asc,price_desc'],
        ];
    }
}