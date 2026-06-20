<?php

namespace App\DTOs\Customer\Shop;

/**
 * DTO Inmutable para la Landing Page.
 * Garantiza que el branchId sea un UUID válido antes de llegar a la Acción.
 */
readonly class LandingQueryDTO
{
    public function __construct(
        public string $branchId
    ) {}

    /**
     * Única puerta de entrada. 
     * En este caso, el branchId se resuelve en el controlador vía Service.
     */
    public static function fromRequest(string $branchId): self
    {
        return new self($branchId);
    }
}