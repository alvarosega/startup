<script setup>
import { router, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({
    branches: {
        type: Array,
        required: true
    }
});

/**
 * Despacha la extracción física lógica del nodo de sucursal.
 * Utiliza el método DELETE nativo del enrutador de Inertia.
 */
const deleteBranch = (id) => {
    if (window.confirm('¿Está seguro de extraer este nodo de sucursal de la circulación logística?')) {
        router.delete(route('admin.operations.branches.destroy', id), {
            preserveScroll: true
        });
    }
};
</script>

<template>
    <AdminLayout>
        <template #header>
            Nodos de Sucursales Logísticas
        </template>

        <div class="space-y-4">
            <div class="flex items-center justify-end bg-card p-4 border border-border rounded-md shadow-flat">
                <Link 
                    :href="route('admin.operations.branches.create')"
                    class="admin-btn-primary inline-flex items-center gap-1.5"
                >
                    <span class="material-symbols-rounded text-lg">add_location_alt</span>
                    <span>Nueva Sucursal</span>
                </Link>
            </div>

            <div class="bg-card border border-border rounded-md shadow-flat overflow-x-auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th class="admin-table-th">Sucursal / Nodo</th>
                            <th class="admin-table-th">Ciudad</th>
                            <th class="admin-table-th">Teléfono</th>
                            <th class="admin-table-th">Tarifa Base</th>
                            <th class="admin-table-th">Precio por KM</th>
                            <th class="admin-table-th text-center">Por Defecto</th>
                            <th class="admin-table-th text-center">Estado</th>
                            <th class="admin-table-th text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="branches.length === 0">
                            <td colspan="8" class="admin-table-td text-center text-muted-foreground py-6 font-normal">
                                No existen sucursales operativas materializadas en el sistema.
                            </td>
                        </tr>
                        <tr v-for="branch in branches" :key="branch.id" class="admin-table-tr">
                            <td class="admin-table-td font-bold">
                                {{ branch.name }}
                            </td>
                            <td class="admin-table-td">
                                {{ branch.city }}
                            </td>
                            <td class="admin-table-td font-mono text-xs">
                                {{ branch.phone || 'N/A' }}
                            </td>
                            <td class="admin-table-td font-mono text-xs">
                                {{ Number(branch.delivery_base_fee).toFixed(2) }}
                            </td>
                            <td class="admin-table-td font-mono text-xs">
                                {{ Number(branch.delivery_price_per_km).toFixed(2) }}
                            </td>
                            <td class="admin-table-td text-center">
                                <span v-if="branch.is_default" class="badge-info">Principal</span>
                                <span v-else class="text-muted-foreground/40 text-xs">—</span>
                            </td>
                            <td class="admin-table-td text-center">
                                <span :class="branch.is_active ? 'badge-success' : 'badge-error'">
                                    {{ branch.is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="admin-table-td text-right">
                                <div class="inline-flex items-center gap-2">
                                    <Link
                                        :href="route('admin.operations.branches.edit', branch.id)"
                                        class="px-2 py-1 bg-secondary text-secondary-foreground text-xs font-semibold rounded border border-border hover:bg-neutral-200 dark:hover:bg-neutral-800 transition-colors"
                                    >
                                        Configurar
                                    </Link>
                                    <button
                                        type="button"
                                        @click="deleteBranch(branch.id)"
                                        class="px-2 py-1 bg-destructive/5 text-destructive border border-destructive/20 text-xs font-semibold rounded hover:bg-destructive/10 transition-colors"
                                    >
                                        Eliminar
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>