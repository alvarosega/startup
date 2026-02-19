<?php

namespace App\Actions\Customer\Auth;

use App\Models\Customer;
use App\DTOs\Customer\Auth\RegisterCustomerData;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\Geo\BranchCoverageService;

class RegisterCustomerAction
{
    public function __construct(
        protected BranchCoverageService $geoService
    ) {}
    public function execute(RegisterCustomerData $data): Customer
    {
        // Calculamos cobertura una sola vez
        $assignedBranchId = $this->geoService->identifyBranch($data->latitude, $data->longitude);
    
        return DB::transaction(function () use ($data, $assignedBranchId) {
            
            // TABLA 1: customers (Credenciales y Contexto)
            $customer = Customer::create([
                'phone'        => $data->phone,
                'email'        => $data->email,
                'password'     => Hash::make($data->password),
                'country_code' => strtoupper($data->countryCode), // <--- DINÁMICO DESDE DTO
                'branch_id'    => $assignedBranchId,
                'is_active'    => true,
            ]);
    
            // TABLA 2: customer_profiles (Datos Personales y Estética)
            $customer->profile()->create([
                'first_name'    => $data->firstName,
                'last_name'     => $data->lastName,
                'avatar_type'   => $data->avatarType,
                'avatar_source' => $data->avatarSource ?? 'avatar_1.svg',
            ]);
    
            // TABLA 3: customer_addresses (Logística)
            $customer->addresses()->create([
                'alias'      => $data->alias ?? 'Mi Ubicación',
                'address'    => $data->address,
                'reference'  => $data->details,
                'latitude'   => $data->latitude,
                'longitude'  => $data->longitude,
                'branch_id'  => $assignedBranchId,
                'is_default' => true,
            ]);
    
            $customer->assignRole('customer');
    
            return $customer;
        });
    }
}