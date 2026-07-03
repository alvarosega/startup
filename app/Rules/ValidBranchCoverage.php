<?php

declare(strict_types=1);

namespace App\Rules;

use App\Services\Customer\Queries\BranchQueryService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class ValidBranchCoverage implements ValidationRule
{
    public function __construct(
        private readonly BranchQueryService $branchQueryService,
        private readonly ?float $latitude,
        private readonly ?float $longitude
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->latitude === null || $this->longitude === null) {
            $fail('Se requieren coordenadas satelitales válidas para verificar la cobertura logística.');
            return;
        }

        $colliding = $this->branchQueryService->getCollidingBranches($this->latitude, $this->longitude);

        if (empty($colliding)) {
            if ($value !== null) {
                $fail('La ubicación se encuentra fuera de la zona de cobertura. No se permite la asignación de sucursal.');
            }
            return;
        }

        if (count($colliding) === 1) {
            if ($value !== $colliding[0]['id']) {
                $fail('La sucursal seleccionada no coincide con la demarcación única de cobertura para esa zona.');
            }
            return;
        }

        $validIds = array_column($colliding, 'id');
        if ($value === null || !in_array($value, $validIds, true)) {
            $fail('Su ubicación se encuentra en una intersección logística. Debe designar una sucursal válida de la lista.');
        }
    }
}