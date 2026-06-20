<?php

namespace App\Http\Controllers\Web\Admin\Dashboard;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Muestra la vista principal de la consola de administración.
     * Por ahora, solo entrega una vista base para el aterrizaje.
     */
    public function index(): Response
    {
        return Inertia::render('Admin/Dashboard/Index', [
            'system_status' => 'ONLINE',
            'last_sync' => now()->format('Y-m-d H:i:s'),
        ]);
    }
}