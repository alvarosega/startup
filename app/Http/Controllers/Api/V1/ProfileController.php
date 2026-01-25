<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

// Reutilización total de la arquitectura
use App\Actions\Profile\UpdateUserProfile;
use App\Actions\Profile\UpdateUserAvatar;
use App\DTOs\Profile\UpdateProfileData;
use App\DTOs\Profile\UpdateAvatarData;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Requests\Profile\UpdateAvatarRequest;
use App\Http\Resources\ProfileResource;

class ProfileController extends Controller
{
    public function show(): JsonResponse
    {
        return response()->json([
            'data' => new ProfileResource(Auth::user())
        ]);
    }

    public function update(UpdateProfileRequest $request, UpdateUserProfile $action): JsonResponse
    {
        $data = UpdateProfileData::fromRequest($request);
        $user = $action->execute($request->user(), $data);

        return response()->json([
            'message' => 'Profile updated successfully',
            'data' => new ProfileResource($user)
        ]);
    }

    public function updateAvatar(UpdateAvatarRequest $request, UpdateUserAvatar $action): JsonResponse
    {
        $data = UpdateAvatarData::fromRequest($request);
        $user = $action->execute($request->user(), $data);

        return response()->json([
            'message' => 'Avatar updated successfully',
            'data' => new ProfileResource($user)
        ]);
    }
}