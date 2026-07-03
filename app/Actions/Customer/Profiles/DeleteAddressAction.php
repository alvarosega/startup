<?php

declare(strict_types=1);

namespace App\Actions\Customer\Profiles;

use App\Architecture\ActionResult;
use App\Models\Users\CustomerAddress;
use Illuminate\Support\Facades\DB;

final class DeleteAddressAction
{
    public function execute(string $customerId, string $addressId): ActionResult
    {
        return DB::transaction(function () use ($customerId, $addressId): ActionResult {
            /** @var CustomerAddress|null $address */
            $address = CustomerAddress::query()
                ->where('id', $addressId)
                ->where('customer_id', $customerId)
                ->where('deleted_epoch', 0)
                ->first();

            if (!$address) {
                return ActionResult::failure('La ubicación seleccionada no existe o ya ha sido removida.');
            }

            // Aplicación de la regla de negocio inquebrantable
            if ((bool) $address->is_default) {
                return ActionResult::failure('Operación denegada: No se puede eliminar la dirección marcada como predeterminada. Designe otra ubicación como preferida antes de proceder.');
            }

            $address->update([
                'deleted_epoch' => time(),
                'is_default'    => false,
            ]);

            return ActionResult::success($address);
        });
    }
}