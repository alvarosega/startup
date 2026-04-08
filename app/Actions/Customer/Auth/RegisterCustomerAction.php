<?php

namespace App\Actions\Customer\Auth;

use App\Models\Customer;
use App\DTOs\Customer\Auth\RegisterCustomerData;
use App\Services\Geo\BranchCoverageService;
use App\Services\ShopContextService;
use App\Services\Cart\CartService;
use App\Exceptions\IdentityCollisionException;
use Illuminate\Support\Facades\{DB, Hash};

class RegisterCustomerAction
{
    public function __construct(
        protected ShopContextService $shopContext,
        protected BranchCoverageService $geoService,
        protected CartService $cartService
    ) {}

    public function execute(RegisterCustomerData $data, ?string $idempotencyKey = null): Customer
    {
        if ($idempotencyKey && $cached = cache()->get("reg_key_{$idempotencyKey}")) {
            return $cached;
        }

        return DB::transaction(function () use ($data, $idempotencyKey) {
            
            $this->validateGlobalUniqueness($data->email, $data->phone);

            if ($idempotencyKey) {
                $duplicate = Customer::where('idempotency_key', $idempotencyKey)->lockForUpdate()->first();
                if ($duplicate) return $duplicate;
            }

            $assignedBranchId = $this->geoService->identifyBranch($data->latitude, $data->longitude) 
                                ?? $this->shopContext->getDefaultBranchId();

            $customer = Customer::create([
                'phone'           => $data->phone,
                'email'           => $data->email,
                'password'        => Hash::make($data->password),
                'country_code'    => $data->countryCode,
                'branch_id'       => $assignedBranchId,
                'latitude'        => $data->latitude,
                'longitude'       => $data->longitude,
                'is_active'       => true,
                'idempotency_key' => $idempotencyKey,
            ]);

            $customer->profile()->create([
                'first_name'    => mb_convert_encoding($data->firstName, 'UTF-8'),
                'last_name'     => mb_convert_encoding($data->lastName, 'UTF-8'),
                'avatar_type'   => 'icon', 
                'avatar_source' => $data->avatarSource, // ej: "avatar_3.png"
            ]);

            $customer->addresses()->create([
                'alias'      => $data->alias,
                'address'    => $data->address,
                'reference'  => $data->details,
                'latitude'   => $data->latitude,
                'longitude'  => $data->longitude,
                'branch_id'  => $assignedBranchId,
                'is_default' => true,
            ]);

            $customer->assignRole('customer');

            if ($data->guestUuid) {
                $this->cartService->fusionGuestCart((string) $customer->id, $data->guestUuid);
            }

            if ($idempotencyKey) {
                cache()->put("reg_key_{$idempotencyKey}", $customer, 600);
            }

            return $customer;
        });
    }

    private function validateGlobalUniqueness(string $email, string $phone): void
    {
        foreach (['admins', 'customers', 'drivers'] as $table) {
            $exists = DB::table($table)
                ->where('email', $email)
                ->orWhere('phone', $phone)
                ->lockForUpdate()
                ->exists();

            if ($exists) {
                throw new IdentityCollisionException("El correo o teléfono ya están registrados en la plataforma.");
            }
        }
    }
}