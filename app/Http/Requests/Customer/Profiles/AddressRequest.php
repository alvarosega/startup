<?php

declare(strict_types=1);

namespace App\Http\Requests\Customer\Profiles;

use App\Rules\ValidBranchCoverage;
use App\Services\Customer\Queries\BranchQueryService;
use App\Services\Customer\Queries\CustomerQueryService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

final class AddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(
        BranchQueryService $branchQueryService,
        CustomerQueryService $customerQueryService
    ): array {
        /** @var string $customerId */
        $customerId = Auth::guard('customer')->id();

        // Control perimetral preventivo del límite de 10 direcciones (Solo en inserción)
        if (!$this->route('id') && $customerQueryService->countActiveAddresses($customerId) >= 10) {
            throw ValidationException::withMessages([
                'limit' => 'Límite de 10 direcciones alcanzado. Debe eliminar una ubicación existente antes de registrar una nueva.',
            ]);
        }

        $lat = $this->filled('latitude') ? (float) $this->input('latitude') : null;
        $lng = $this->filled('longitude') ? (float) $this->input('longitude') : null;

        return [
            'alias'       => ['required', 'string', 'max:50'],
            'address'     => ['required', 'string', 'max:255'],
            'reference'   => ['nullable', 'string', 'max:255'],
            'latitude'    => ['required', 'numeric', 'between:-90,90'],
            'longitude'   => ['required', 'numeric', 'between:-180,180'],
            'branch_id'   => ['nullable', 'string', 'exists:branches,id'],
            'is_default'  => ['sometimes', 'boolean'],
        ];
    }
}