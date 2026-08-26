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

    public function rules(BranchQueryService $branchQueryService): array
    {
        $lat = $this->filled('latitude') ? (float) $this->input('latitude') : null;
        $lng = $this->filled('longitude') ? (float) $this->input('longitude') : null;

        return [
            'phone'         => ['required', 'string', 'min:8', 'max:20', new \App\Rules\CustomerRegistrationIdentityRule('phone')],
            'email'         => ['required', 'email', 'max:255', new \App\Rules\CustomerRegistrationIdentityRule('email')],
            'password'      => ['required', 'string', 'min:8', 'confirmed'],
            'country_code'  => ['nullable', 'string', 'max:3'],
            'latitude'      => ['required', 'numeric', 'between:-90,90'],
            'longitude'     => ['required', 'numeric', 'between:-180,180'],
            'branch_id'     => ['nullable', 'uuid', new ValidBranchCoverage($branchQueryService, $lat, $lng)],
            'first_name'    => ['required', 'string', 'max:100'],
            'last_name'     => ['required', 'string', 'max:100'],
            'avatar_type'   => ['nullable', 'string', 'max:50'],
            'avatar_source' => ['required', 'string', 'max:255'],
            'alias'         => ['nullable', 'string', 'max:100'],
            'address'       => ['required', 'string', 'max:255'],
            'details'       => ['nullable', 'string', 'max:255'],
        ];
    }
}