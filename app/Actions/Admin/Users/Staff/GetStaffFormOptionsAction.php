<?php

declare(strict_types=1);

namespace App\Actions\Admin\Users\Staff;

use App\Models\Operations\Branch;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class GetStaffFormOptionsAction
{
    /**
     * @return array{branches: array, roles: array, permissions: array}
     */
    public function execute(): array
    {
        $branches = Branch::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'city'])
            ->map(fn ($b) => [
                'id'   => (string) $b->id,
                'name' => (string) $b->name,
                'city' => (string) $b->city,
            ])
            ->all();

        $roles = Role::where('guard_name', 'super_admin')
            ->get(['id', 'name'])
            ->map(fn ($r) => [
                'id'    => (int) $r->id,
                'name'  => (string) $r->name,
                'label' => $this->formatRoleLabel($r->name),
            ])
            ->all();

        $permissions = Permission::where('guard_name', 'super_admin')
            ->get(['id', 'name'])
            ->map(fn ($p) => [
                'id'    => (int) $p->id,
                'name'  => (string) $p->name,
                'label' => $this->formatPermissionLabel($p->name),
            ])
            ->all();

        return [
            'branches'    => $branches,
            'roles'       => $roles,
            'permissions' => $permissions,
        ];
    }

    private function formatRoleLabel(string $role): string
    {
        return match ($role) {
            'super_admin'      => 'Administrador Maestro (Global)',
            'branch_manager'   => 'Gerente de Sucursal',
            'branch_warehouse' => 'Operador de Almacén / Stock',
            'branch_logistics' => 'Operador de Despacho / Logística',
            'branch_cashier'   => 'Operador de Caja',
            default            => ucwords(str_replace('_', ' ', $role)),
        };
    }

    private function formatPermissionLabel(string $perm): string
    {
        return match ($perm) {
            'manage_catalog'          => 'Gestión de Catálogo (Productos, Categorías, Marcas)',
            'manage_branches'         => 'Gestión de Sucursales y Zonas',
            'manage_branch_schedules' => 'Gestión de Horarios y Excepciones',
            'manage_branch_cash'      => 'Control y Arqueo de Caja Diaria',
            'manage_inventory'        => 'Control de Stock, Kardex y Lotes',
            'manage_prices'           => 'Gestión de Precios Masivos',
            'manage_purchases'        => 'Ingresos y Compras a Proveedores',
            'manage_removals'         => 'Registro de Mermas y Bajas',
            'manage_orders'           => 'Gestión y Aprobación de Órdenes',
            'manage_dispatch'         => 'Despacho y Entrega a Repartidores',
            'view_logistics_radar'    => 'Visualización del Radar en Vivo',
            'manage_users'            => 'Gestión de Clientes y Personal',
            'manage_drivers'          => 'Aprobación y Gestión de Repartidores',
            'view_admin_dashboard'    => 'Acceso a Panel de Control',
            default                   => ucwords(str_replace('_', ' ', $perm)),
        };
    }
}
