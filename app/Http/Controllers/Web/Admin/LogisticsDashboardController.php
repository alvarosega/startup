<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\TransferRequest;
use App\Models\InventoryRemoval;
use App\Models\LoteInventario;
use Inertia\Inertia;

class LogisticsDashboardController extends Controller
{
    public function index()
    {
        // 1. Datos críticos para el Operario
        $userBranch = auth()->user()->branch_id;

        return Inertia::render('Admin/Logistics/Dashboard', [
            // Transferencias que llegan a SU sucursal y están pendientes
            'incoming_transfers' => TransferRequest::where('destination_branch_id', $userBranch)
                ->where('estado', 'transito')
                ->count(),
            
            // Sus solicitudes de baja pendientes de aprobación
            'pending_removals' => InventoryRemoval::where('branch_id', $userBranch)
                ->where('estado', 'pendiente')
                ->count(),
            
            // Productos por vencer en los próximos 30 días en SU sucursal
            'expiring_products' => LoteInventario::where('branch_id', $userBranch)
                ->where('cantidad_fisica', '>', 0)
                ->whereBetween('fecha_vencimiento', [now(), now()->addDays(30)])
                ->count()
        ]);
    }
}