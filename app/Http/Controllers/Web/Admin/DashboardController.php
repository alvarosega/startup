<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
// Importamos modelos si queremos datos reales (opcional por ahora)
use App\Models\User; 
// use App\Models\Product; 

class DashboardController extends Controller
{
    public function index(): Response
    {
        // 1. Preparamos los datos que la vista Vue espera.
        // El error dice que busca "total_users", asumimos que está dentro de un objeto "stats"
        // o en la raíz. Mandamos ambos por seguridad.
        
        $stats = [
            'total_users' => 12,      // Valor de ejemplo
            'total_products' => 145,  // Valor de ejemplo
            'active_orders' => 5,     // Valor de ejemplo
            'low_stock' => 3,         // Valor de ejemplo
            'revenue' => 1500.00      // Valor de ejemplo
        ];

        // 2. Renderizamos apuntando a la carpeta que me dijiste: 'AdminDasboards'
        return Inertia::render('Admin/Dashboards/SuperAdmin', [
            
            // Pasamos el objeto 'stats' para que Vue pueda hacer {{ stats.total_users }}
            'stats' => $stats,

            // También pasamos 'total_users' suelto por si tu vista lo pide directamente
            'total_users' => 12, 
        ]);
    }
}
