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
                'internal_code' => 'PROV-001',
                'contact_name' => 'Carlos Distribución',
                'email_orders' => 'pedidos@cbn.bo',
                'phone' => '70010001',
                'address' => 'Av. Montes Nro. 400, Zona Industrial',
                'city' => 'La Paz',
                'lead_time_days' => 2, // Entregan en 2 días
                'min_order_value' => 500.00,
                'credit_days' => 7,
                'credit_limit' => 10000.00,
                'is_active' => true,
            ],
            [
                'company_name' => 'Embotelladoras Bolivianas Unidas S.A.',
                'commercial_name' => 'EMBOL (Coca-Cola)',
                'tax_id' => '1002003004',
                'internal_code' => 'PROV-002',
                'contact_name' => 'Agencia Central',
                'email_orders' => 'preventa@embol.com',
                'phone' => '70010002',
                'address' => 'Av. 6 de Marzo, El Alto',
                'city' => 'El Alto',
                'lead_time_days' => 1, // Entrega rápida
                'min_order_value' => 200.00,
                'credit_days' => 15,
                'credit_limit' => 15000.00,
                'is_active' => true,
            ],
            [
                'company_name' => 'Distribuidora & Mercadeo S.A.',
                'commercial_name' => 'D&M (Licores Premium)',
                'tax_id' => '5060708090',
                'internal_code' => 'PROV-003',
                'contact_name' => 'Ejecutivo Cuentas Clave',
                'email_orders' => 'ventas@dym.com.bo',
                'phone' => '70010003',
                'address' => 'Barrio Sirari, Calle Flamboyanes',
                'city' => 'Santa Cruz',
                'lead_time_days' => 5, // Tardan más
                'min_order_value' => 1000.00, // Pedido mínimo alto
                'credit_days' => 30, // Buen crédito
                'credit_limit' => 20000.00,
                'is_active' => true,
            ],
            [
                'company_name' => 'Sociedad Agroindustrial del Valle Ltda.',
                'commercial_name' => 'Casa Real',
                'tax_id' => '3040506070',
                'internal_code' => 'PROV-004',
                'contact_name' => 'Ventas Directas',
                'email_orders' => 'pedidos@casareal.bo',
                'phone' => '70010004',
                'address' => 'Santa Ana de la Nueva, Tarija',
                'city' => 'Tarija',
                'lead_time_days' => 3,
                'min_order_value' => 300.00,
                'credit_days' => 0, // Contado
                'credit_limit' => 0.00,
                'is_active' => true,
            ],
            [
                'company_name' => 'Hipermaxi S.A.',
                'commercial_name' => 'Hipermaxi Mayorista',
                'tax_id' => '9988776655',
                'internal_code' => 'PROV-005',
                'contact_name' => 'Caja Central',
                'email_orders' => 'mayorista@hipermaxi.com',
                'phone' => '70010005',
                'address' => 'Av. Cristo Redentor 4to Anillo',
                'city' => 'Santa Cruz',
                'lead_time_days' => 0, // Entrega inmediata (pickup)
                'min_order_value' => 0.00,
                'credit_days' => 0,
                'credit_limit' => 0.00,
                'is_active' => true,
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