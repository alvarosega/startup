<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Driver\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Driver\Profile\UploadDocsRequest; 
use App\DTOs\Driver\Profile\UploadDocsData;
use App\Actions\Driver\Profile\UploadDocsAction;
use App\Http\Resources\Driver\Profile\DriverProfileResource;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class DriverProfileController extends Controller
{
    public function index(): Response
    {
        $driver = Auth::guard('driver')->user();
        
        // Prevención de N+1
        $driver->load(['profile', 'branch']);

        return Inertia::render('Driver/Profile/Index', [
            // resolve() extrae el array plano. El frontend solo necesitará el prop 'driver'
            'driver' => (new DriverProfileResource($driver))->resolve(),
        ]);
    }

    public function uploadDocs(UploadDocsRequest $request, UploadDocsAction $action): RedirectResponse
    {
        $driver = Auth::guard('driver')->user();
        
        // Bloqueo de seguridad: Inmutabilidad post-aprobación
        if ($driver->status === 'approved') {
            return back()->with('error', 'Operación denegada. Tu cuenta ya está operativa y los documentos no pueden ser alterados.');
        }

        $data = UploadDocsData::fromRequest($request);
        $action->execute($data, $driver);

        return back()->with('success', 'Documentos enviados a revisión.');
    }
}