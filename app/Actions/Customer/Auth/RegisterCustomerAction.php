<?php

declare(strict_types=1);

namespace App\Actions\Customer\Auth;

use App\Models\Users\Customer;
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

            $assignedBranchId = $data->branchId 
                ?? $this->geoService->identifyBranch($data->latitude, $data->longitude);

                $customer = Customer::create([
                    'phone'               => $data->phone,
                    'email'               => $data->email,
                    'password'            => Hash::make($data->password),
                    'country_code'        => $data->countryCode,
                    'branch_id'           => $assignedBranchId,
                    'last_known_location' => DB::raw("ST_GeomFromText('POINT({$data->latitude} {$data->longitude})')"),
                    'is_active'           => true,
                    'idempotency_key'     => $idempotencyKey,
                ]);
                
                $customer->profile()->create([
                    'first_name'    => mb_convert_encoding($data->firstName, 'UTF-8'),
                    'last_name'     => mb_convert_encoding($data->lastName, 'UTF-8'),
                    'avatar_type'   => $data->avatarType, 
                    'avatar_source' => $data->avatarSource, 
                ]);
                
                // CORRECCIÓN: Inserción directa en base de datos para saltar la interferencia de PointCast con DB::raw
                DB::table('customer_addresses')->insert([
                    'id'          => (string) \Illuminate\Support\Str::uuid7(),
                    'customer_id' => (string) $customer->id,
                    'branch_id'   => $assignedBranchId,
                    'alias'       => $data->alias,
                    'address'     => $data->address,
                    'reference'   => $data->details,
                    'position'    => DB::raw("ST_GeomFromText('POINT({$data->latitude} {$data->longitude})')"),
                    'is_default'  => true,
                    'created_at'  => now(),
                    'updated_at'  => now(),
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
        $tables = ['admins', 'customers', 'drivers'];

        foreach ($tables as $table) {
            $emailQuery = DB::table($table)->where('email', $email);
            $phoneQuery = DB::table($table)->where('phone', $phone);

            if ($table === 'drivers') {
                $emailQuery->whereNull('deleted_at');
                $phoneQuery->whereNull('deleted_at');
            }

            if ($emailQuery->lockForUpdate()->exists() || $phoneQuery->lockForUpdate()->exists()) {
                throw new IdentityCollisionException("El correo o teléfono ya están registrados en la plataforma.");
            }
        }
    }
}