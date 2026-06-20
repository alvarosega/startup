<?php  

namespace App\Http\Requests\Customer\Category;

use Illuminate\Foundation\Http\FormRequest;

class GetCategoryRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'slug' => ['required', 'string', 'exists:categories,slug,is_active,1'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['slug' => $this->route('slug')]);
    }
}