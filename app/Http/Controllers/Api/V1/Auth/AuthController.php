<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

// Actions (Lógica de Negocio Reutilizable)
use App\Actions\Auth\LoginUser;
use App\Actions\Auth\RegisterUser;

// DTOs (Datos Tipados)
use App\DTOs\Auth\LoginData;
use App\DTOs\Auth\RegisterData;

// Requests (Validación)
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;

// Resources (Salida JSON Estandarizada)
use App\Http\Resources\UserResource;

class AuthController extends Controller
{
    /**
     * Inicio de Sesión API.
     * Devuelve un Token (Sanctum) en lugar de una Cookie de Sesión.
     */
    public function login(LoginRequest $request, LoginUser $action): JsonResponse
    {
        // 1. Seguridad: Verificar Rate Limiting
        $request->ensureIsNotRateLimited();

        try {
            // 2. Preparar Datos (DTO)
            $data = LoginData::fromRequest($request);

            // 3. Ejecutar Acción (Misma lógica que en Web)
            // Retorna array con ['user' => $user, 'token' => $plainTextToken]
            // NOTA: Para la API, LoginUser ya genera el token internamente si se diseñó así,
            // pero si la acción LoginUser retorna un array genérico, aquí extraemos lo necesario.
            // Según tu implementación previa de LoginUser, retorna ['user' => ..., 'token' => ...]
            $result = $action->execute($data);

            // 4. Limpiar Rate Limiter tras éxito
            $this->clearLoginRateLimiter($request);

            // 5. Respuesta JSON Estricta
            return response()->json([
                'message' => 'Login successful',
                'data' => [
                    'user' => new UserResource($result['user']),
                    'token' => $result['token'], // El token ya viene generado por la Action
                    'token_type' => 'Bearer',
                ],
            ]);

        } catch (\Exception $e) {
            // Registrar fallo en Rate Limiter
            $request->hitRateLimiter();
            
            // Re-lanzar para que Laravel maneje el código de error (422 o 401)
            throw $e;
        }
    }

    /**
     * Registro de Usuario API.
     * Reutiliza exactamente la misma lógica de negocio que el registro Web.
     */
    public function register(RegisterRequest $request, RegisterUser $action): JsonResponse
    {
        // 1. Preparar Datos (DTO)
        // El Request ya validó formato de teléfono, duplicados, etc.
        $data = RegisterData::fromRequest($request);

        // 2. Ejecutar Acción (Transaction, Avatar, Cart Merge, Roles)
        $user = $action->execute($data);

        // 3. Generar Token para acceso inmediato (Sin necesidad de login posterior)
        // Usamos el device_name que viene en el request o un default
        $deviceName = $request->input('device_name', 'Unknown Mobile Device');
        $token = $user->createToken($deviceName)->plainTextToken;

        // 4. Respuesta 201 Created
        return response()->json([
            'message' => 'User registered successfully',
            'data' => [
                'user' => new UserResource($user),
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ], 201);
    }

    /**
     * Helper privado para limpiar el limitador de intentos.
     */
    private function clearLoginRateLimiter(LoginRequest $request): void
    {
        $throttleKey = Str::transliterate(Str::lower($request->input('phone')) . '|' . $request->ip());
        RateLimiter::clear($throttleKey);
    }
}