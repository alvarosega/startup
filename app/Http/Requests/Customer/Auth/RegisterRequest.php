<?php

declare(strict_types=1);

namespace App\Http\Requests\Customer\Auth;

use App\Rules\ValidBranchCoverage;
use App\Services\Customer\Queries\BranchQueryService;
use App\Traits\ValidatesGlobalIdentity;
use Illuminate\Foundation\Http\FormRequest;

final class RegisterRequest extends FormRequest
{
    use ValidatesGlobalIdentity;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->normalizeIdentityData();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(BranchQueryService $branchQueryService): array
    {
        $lat = $this->filled('latitude') ? (float) $this->input('latitude') : null;
        $lng = $this->filled('longitude') ? (float) $this->input('longitude') : null;

        return [
            'phone' => $this->globalPhoneRules('customers'),
            'email' => $this->globalEmailRules('customers'),
            'password' => ['required', 'string', 'min:8'],
            'country_code' => ['required', 'string', 'max:3'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'branch_id' => ['nullable', 'uuid', new ValidBranchCoverage($branchQueryService, $lat, $lng)],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'avatar_type' => ['required', 'string', 'max:50'],
            'avatar_source' => ['required', 'string', 'max:255'],
            'alias' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'details' => ['nullable', 'string', 'max:255'],
        ];
    }
}