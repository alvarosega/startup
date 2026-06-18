<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\ShopContextService;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureCustomerHasCoverage
{
    public function __construct(
        protected readonly ShopContextService $shopContext
    ) {}

    /**
     * Intercepta peticiones de modificación de carrito para usuarios fuera de cobertura.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Protocolo de exclusión: Los invitados operan bajo el fallback y no se bloquean a nivel de cuenta
        if (!Auth::guard('customer')->check()) {
            return $next($request);
        }

        // Validación pesimista en Backend: Si el perfil del cliente resuelve Modo Catálogo, se veta la mutación
        if ($this->shopContext->isUserOutOfCoverage()) {
            return response()->json([
                'success' => false,
                'code'    => 'OUT_OF_COVERAGE_RESTRICTION',
                'message' => 'Operación denegada. Tu ubicación principal registrada se encuentra fuera de nuestra área de cobertura logística. Registra o selecciona una dirección válida para desbloquear el carrito.'
            ], 403);
        }

        return $next($request);
    }
}