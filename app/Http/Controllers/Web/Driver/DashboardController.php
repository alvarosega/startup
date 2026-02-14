<?php

namespace App\Http\Controllers\Web\Driver;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $driver = Auth::guard('driver')->user();
        
        // Cargamos los detalles para saber el estatus y documentos
        $driver->load('details');

        return Inertia::render('Driver/Dashboard', [
            'driver' => [
                'id' => bin2hex($driver->getRawOriginal('id')),
                'status' => $driver->status,
                'has_documents' => $this->checkDocuments($driver->details),
                // URLs de ejemplo para la vista (ajusta según tu storage)
                'ci_front_url' => $driver->details->ci_front_path ?? null, 
            ],
            'pendingOrders' => [] // Por ahora vacío
        ]);
    }

    private function checkDocuments($details)
    {
        // Lógica para saber si ya subió sus fotos
        return !empty($details->ci_front_path) && !empty($details->license_photo_path);
    }
}