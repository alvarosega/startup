<?php

namespace App\Http\Controllers\Web\Driver\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Driver\Profile\UploadDocsRequest;
use App\DTOs\Driver\Profile\UploadDocsData;
use App\Actions\Driver\Profile\UploadDocsAction;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DriverProfileController extends Controller
{
    public function index()
    {
        $driver = Auth::guard('driver')->user();
        $driver->load(['details', 'branch']);

        // Utilizamos el estado principal del conductor como fuente de verdad
        $status = $driver->status; 
        
        // Verificamos si los documentos ya existen en el registro
        $hasDocs = !empty($driver->details->ci_front_path) && !empty($driver->details->license_photo_path);

        // SIEMPRE retornamos la misma vista. El frontend decidirá qué pintar basado en las variables.
        return Inertia::render('Driver/Profile/Index', [
            'driver'  => $driver,
            'status'  => $status,
            'hasDocs' => $hasDocs
        ]);
    }

    public function uploadDocs(UploadDocsRequest $request, UploadDocsAction $action)
    {
        $driver = Auth::guard('driver')->user();
        $data = UploadDocsData::fromRequest($request);
        $action->execute($data, $driver);

        // Cambiamos el estado a 'pending' (o 'under_review' según tu lógica) para indicar que subió docs
        $driver->update(['status' => 'pending']); 

        return back()->with('success', 'Documentos enviados a revisión.');
    }
}