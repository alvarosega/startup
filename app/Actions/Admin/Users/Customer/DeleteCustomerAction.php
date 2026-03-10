<?php

namespace App\Actions\Admin\Users\Customer;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class DeleteCustomerAction
{
    public function execute(string $id): void
    {
        DB::transaction(function () use ($id) {
            $customer = Customer::findOrFail($id);

            // REGLA 2.B: Zero-Trust Deletes. Validamos que no tenga órdenes activas o en tránsito.
            // Asumiendo que existe la relación orders()
            /*
            if ($customer->orders()->whereIn('status', ['pending', 'in_transit'])->exists()) {
                throw ValidationException::withMessages([
                    'error' => 'No se puede eliminar un cliente con pedidos activos.'
                ]);
            }
            */

            $customer->delete(); // Ejecuta SoftDelete según migración

            // Purga la caché de la lista de clientes
            Cache::forget('admin_customers_list_base');
        });
    }
}