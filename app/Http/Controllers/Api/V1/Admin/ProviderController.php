<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use Illuminate\Http\JsonResponse;

use App\DTOs\Provider\ProviderData;
use App\Http\Requests\Provider\StoreProviderRequest;
use App\Http\Requests\Provider\UpdateProviderRequest;
use App\Actions\Provider\CreateProvider;
use App\Actions\Provider\UpdateProvider;
use App\Http\Resources\ProviderResource;

class ProviderController extends Controller
{
    public function index(): JsonResponse
    {
        $providers = Provider::paginate(20);
        return response()->json(ProviderResource::collection($providers)->response()->getData(true));
    }

    public function store(StoreProviderRequest $request, CreateProvider $action): JsonResponse
    {
        $data = ProviderData::fromRequest($request);
        $provider = $action->execute($data);
        return response()->json(new ProviderResource($provider), 201);
    }

    public function show(Provider $provider): JsonResponse
    {
        return response()->json(new ProviderResource($provider));
    }

    public function update(UpdateProviderRequest $request, Provider $provider, UpdateProvider $action): JsonResponse
    {
        $data = ProviderData::fromRequest($request);
        $provider = $action->execute($provider, $data);
        return response()->json(new ProviderResource($provider));
    }

    public function destroy(Provider $provider): JsonResponse
    {
        $provider->delete();
        return response()->json(['message' => 'Provider deleted']);
    }
}