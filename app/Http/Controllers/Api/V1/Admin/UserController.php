<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;

// --- ARQUITECTURA REUTILIZADA (La misma que en Web) ---
use App\DTOs\User\UserData;
use App\Actions\User\CreateUser;
use App\Actions\User\UpdateUser;
use App\Http\Requests\Admin\User\StoreUserRequest;
use App\Http\Requests\Admin\User\UpdateUserRequest;
use App\Http\Resources\UserResource;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        // Reutilizamos lógica de query o usamos una simple para API
        $users = User::with(['roles', 'profile', 'branch'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'data' => UserResource::collection($users),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'total' => $users->total(),
            ]
        ]);
    }

    public function store(StoreUserRequest $request, CreateUser $action): JsonResponse
    {
        // 1. DTO
        $data = UserData::fromRequest($request);

        // 2. Acción (Misma lógica que en Web)
        $user = $action->execute($data);

        // 3. Respuesta JSON
        return response()->json([
            'message' => 'User created successfully',
            'data' => new UserResource($user),
        ], 201);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json([
            'data' => new UserResource($user->load(['profile', 'roles', 'branch']))
        ]);
    }

    public function update(UpdateUserRequest $request, User $user, UpdateUser $action): JsonResponse
    {
        // 1. DTO
        $data = UserData::fromRequest($request);

        // 2. Acción
        $action->execute($user, $data);

        return response()->json([
            'message' => 'User updated successfully',
            'data' => new UserResource($user->fresh()),
        ]);
    }

    public function destroy(User $user): JsonResponse
    {
        if ($user->id === auth()->id()) {
            return response()->json(['error' => 'Cannot delete self'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }
}