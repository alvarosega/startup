<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Provider;

class ProviderSeeder extends Seeder
{
    public function run(): void
    {
        $providers = [
            [
                'company_name' => 'Cervecería Boliviana Nacional S.A.',
                'commercial_name' => 'CBN',
                'tax_id' => '1020304050',
                'contact_name' => 'Carlos Distribución',
                'email_orders' => 'pedidos@cbn.bo', // <--- CORREGIDO (Antes 'email')
                'phone' => '70010001'
            ],
            [
                'company_name' => 'Embotelladoras Bolivianas Unidas S.A.',
                'commercial_name' => 'EMBOL (Coca-Cola)',
                'tax_id' => '1002003004',
                'contact_name' => 'Agencia Central',
                'email_orders' => 'preventa@embol.com', // <--- CORREGIDO
                'phone' => '70010002'
            ],
            [
                'company_name' => 'Distribuidora & Mercadeo S.A.',
                'commercial_name' => 'D&M (Licores Premium)',
                'tax_id' => '5060708090',
                'contact_name' => 'Ejecutivo Cuentas Clave',
                'email_orders' => 'ventas@dym.com.bo', // <--- CORREGIDO
                'phone' => '70010003'
            ],
            [
                'company_name' => 'Sociedad Agroindustrial del Valle Ltda.',
                'commercial_name' => 'Casa Real',
                'tax_id' => '3040506070',
                'contact_name' => 'Ventas Directas',
                'email_orders' => 'pedidos@casareal.bo', // <--- CORREGIDO
                'phone' => '70010004'
            ],
            [
                'company_name' => 'Hipermaxi S.A.',
                'commercial_name' => 'Hipermaxi Mayorista',
                'tax_id' => '9988776655',
                'contact_name' => 'Caja Central',
                'email_orders' => 'mayorista@hipermaxi.com', // <--- CORREGIDO
                'phone' => '70010005'
            ]
        ];

        foreach ($providers as $data) {
            Provider::firstOrCreate(
                ['tax_id' => $data['tax_id']], 
                $data
            );
        }
    }
}