<?php

namespace App\Actions\Customer\Profiles;

use App\Models\{Customer, CustomerAddress};
use Illuminate\Support\Facades\DB;

class DeleteAddressAction
{
    public function __construct(protected \App\Services\ShopContextService $shopContext) {}
    public function execute(Customer $customer, string $addressId): void
    {
        $address = $customer->addresses()->findOrFail($addressId);
    
        DB::transaction(function () use ($customer, $address) {
            $wasDefault = $address->is_default;
            $address->delete();
    
            if ($wasDefault) {
                $newDefault = $customer->addresses()->first();
                
                if ($newDefault) {
                    $newDefault->update(['is_default' => true]);
                    $customer->update([
                        'branch_id' => $newDefault->branch_id,
                        'latitude'  => $newDefault->latitude,
                        'longitude' => $newDefault->longitude,
                    ]);
                } else {
                    // CORRECCIÓN: Si no hay más direcciones, vuelve a la SUCURSAL POR DEFECTO
                    $customer->update([
                        'branch_id' => $this->shopContext->getDefaultBranchId(),
                        'latitude'  => null,
                        'longitude' => null,
                    ]);
                }
            }
        });
    }
}