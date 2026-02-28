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
        $driver->load('details');

        $status = $driver->details->verification_status ?? 'pending';
        
        // Validación estricta de documentos requeridos
        $hasDocs = !empty($driver->details->ci_front_path) && !empty($driver->details->license_photo_path);

        // Si está verificado, entra a la operación real.
        if ($status === 'verified') {
            return Inertia::render('Driver/Dashboard', [
                'driver'        => $driver,
                'pendingOrders' => [] 
            ]);
        }

        // Si no está verificado, se queda atrapado en la pantalla de revisión.
        return Inertia::render('Driver/Verification', [
            'driver'  => $driver,
            'status'  => $status,
            'hasDocs' => $hasDocs
        ]);
    }
    /**
     * Orquesta la subida de documentos.
     */
    public function uploadDocs(UploadDocsRequest $request, UploadDocsAction $action)
    {
        // 1. Obtener la entidad autenticada matemáticamente segura
        $driver = Auth::guard('driver')->user();

        // 2. Transformación a DTO inmutable
        $data = UploadDocsData::fromRequest($request);

        // 3. Ejecución de lógica de negocio y guardado en disco
        $action->execute($data, $driver);

        // 4. Retorno HTTP
        return back()->with('success', 'Documentos enviados a revisión.');
    }
}