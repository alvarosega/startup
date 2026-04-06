<?php

namespace App\Http\Controllers\Web\Driver\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Driver\Profile\UploadDocsRequest; // Debes crearlo
use App\DTOs\Driver\Profile\UploadDocsData;
use App\Actions\Driver\Profile\UploadDocsAction;
use App\Http\Resources\Driver\Profile\DriverProfileResource;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DriverProfileController extends Controller
{
    public function index(): Response
    {
        $driver = Auth::guard('driver')->user();
        $driver->load(['profile', 'branch']);

        return Inertia::render('Driver/Profile/Index', [
            'driver'  => new DriverProfileResource($driver),
            'status'  => $driver->status,
            'hasDocs' => (bool)($driver->profile?->ci_front_path && $driver->profile?->license_photo_path)
        ]);
    }

    public function uploadDocs(UploadDocsRequest $request, UploadDocsAction $action)
    {
        $driver = Auth::guard('driver')->user();
        $data = UploadDocsData::fromRequest($request);
        
        $action->execute($data, $driver);

        return back()->with('success', 'Documentos enviados a revisión.');
    }
}