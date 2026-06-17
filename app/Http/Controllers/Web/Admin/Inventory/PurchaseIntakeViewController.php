<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Provider;
use App\Models\Sku;
use Inertia\Inertia;
use Inertia\Response;

class PurchaseIntakeViewController extends Controller
{
    public function index(): Response
    {
        // Alimenta al formulario de Vue con los catálogos mínimos indexados
        return Inertia::render('Admin/Inventory/PurchaseIntakeForm', [
            'branches' => Branch::select('id', 'name')->get(),
            
            // LEY: Uso de alias SQL para emparejar la columna física con el contrato de datos de Vue
            'providers' => Provider::select('id', 'company_name as name')->get(),
            
            'skus' => Sku::select('id', 'name', 'code')->where('is_active', true)->get(),
        ]);
    }
}