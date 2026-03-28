<?php

namespace App\Actions\Customer\Auth;

use App\Models\Customer;
use App\DTOs\Customer\Auth\RegisterCustomerData;
use App\DTOs\Customer\Cart\SyncCartDTO;
use App\Actions\Customer\Cart\SyncGuestCartAction;
use App\Services\Geo\BranchCoverageService;
use App\Services\ShopContextService;
use App\Exceptions\IdentityCollisionException;
use Illuminate\Support\Facades\{DB, Hash, Storage};

class RegisterCustomerAction
{
    public function __construct(
        protected BranchCoverageService $geoService,
        protected SyncGuestCartAction $syncAction,
        protected ShopContextService $shopContext
    ) {}

    public function execute(RegisterCustomerData $data, ?string $idempotencyKey = null): Customer
    {
        // 1. Idempotencia Nivel L1 (Caché)
        if ($idempotencyKey && $cached = cache()->get("reg_key_{$idempotencyKey}")) {
            return $cached;
        }

        // 2. Gestión de I/O (Fuera de la transacción para mayor velocidad de DB)
        $avatarSource = $data->avatarSource;
        if ($data->avatarType === 'custom' && $data->avatarFile) {
            $path = $data->avatarFile->store('avatars/uploads', 'public');
            $avatarSource = basename($path);
        }

        return DB::transaction(function () use ($data, $idempotencyKey, $avatarSource) {
            
            // 3. Blindaje de Identidad (Bloqueo Pesimista)
            $this->validateGlobalUniqueness($data->email, $data->phone);

            // 4. Verificación de duplicados por Idempotencia en DB
            if ($idempotencyKey) {
                $duplicate = Customer::where('idempotency_key', $idempotencyKey)->lockForUpdate()->first();
                if ($duplicate) return $duplicate;
            }

            $assignedBranchId = $this->geoService->identifyBranch($data->latitude, $data->longitude) 
                                ?? $this->shopContext->getDefaultBranchId();

            // 5. Persistencia
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
                'avatar_type'   => 'icon', // Forzado a icon
                'avatar_source' => $data->avatarSource,
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

            // 6. Sincronización Post-Persistencia
            if ($data->guestUuid) {
                $this->syncAction->execute(new SyncCartDTO(
                    customerId: $customer->id,
                    guestUuid:  $data->guestUuid,
                    branchId:   $assignedBranchId
                ));
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
                throw new IdentityCollisionException("Conflicto de identidad detectado en silo.");
            }
        }
    }
}