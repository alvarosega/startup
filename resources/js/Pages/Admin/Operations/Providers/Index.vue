<script setup>
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    providers: {
        type: Object,
        required: true
    },
    filters: {
        type: Object,
        required: true
    },
    can_manage: {
        type: Boolean,
        required: true
    }
});

const searchFilter = ref(props.filters.search || '');

/**
 * Despacha la consulta de búsqueda al backend preservando el estado de la interfaz.
 */
const applyFilter = () => {
    router.get(route('admin.operations.providers.index'), {
        search: searchFilter.value || undefined
    }, {
        preserveState: true,
        replace: true
    });
};

/**
 * Limpia los filtros y reestablece el listado completo.
 */
const clearFilter = () => {
    searchFilter.value = '';
    applyFilter();
};

/**
 * Ejecuta el borrado lógico del proveedor del sistema.
 */
const deleteProvider = (id) => {
    if (window.confirm('¿Está seguro de purgar este proveedor del ecosistema operacional?')) {
        router.delete(route('admin.operations.providers.destroy', { provider: id }), {
            preserveScroll: true
        });
    }
};
</script>

<template>
    <AdminLayout>
        <template #header>
            Socios Comerciales y Abastecimiento
        </template>

        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-card p-4 border border-border rounded-md shadow-flat">
                <div class="flex items-center gap-2 max-w-md w-full">
                    <input 
                        v-model="searchFilter"
                        type="text"
                        placeholder="Buscar por Razón Social, Nombre Comercial o NIT..."
                        class="admin-input"
                        @keyup.enter="applyFilter"
                    />
                    <button 
                        type="button"
                        @click="applyFilter"
                        class="px-3 py-2 bg-secondary text-secondary-foreground font-semibold rounded-md text-sm hover:bg-neutral-200 dark:hover:bg-neutral-800 transition-colors shrink-0"
                    >
                        Buscar
                    </button>
                    <button 
                        type="button"
                        @click="clearFilter"
                        class="px-3 py-2 bg-neutral-100 dark:bg-neutral-800 text-muted-foreground text-xs font-medium rounded-md border border-border hover:bg-neutral-200"
                    >
                        Limpiar
                    </button>
                </div>

                <div v-if="can_manage" class="shrink-0">
                    <Link 
                        :href="route('admin.operations.providers.create')"
                        class="admin-btn-primary inline-flex items-center gap-1.5"
                    >
                        <span class="material-symbols-rounded text-lg">domain_add</span>
                        <span>Incorporar Proveedor</span>
                    </Link>
                </div>
            </div>

            <div class="bg-card border border-border rounded-md shadow-flat overflow-x-auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th class="admin-table-th">Código Interno</th>
                            <th class="admin-table-th">Razón Social / Comercial</th>
                            <th class="admin-table-th">Identificación Tributaria (NIT)</th>
                            <th class="admin-table-th">Contacto Operativo</th>
                            <th class="admin-table-th font-mono text-xs">Tiempo Entrega (Días)</th>
                            <th class="admin-table-th font-mono text-xs">Orden Mínima</th>
                            <th class="admin-table-th text-center">Estado</th>
                            <th class="admin-table-th text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="providers.items.length === 0">
                            <td colspan="8" class="admin-table-td text-center text-muted-foreground py-6 font-normal">
                                No se encontraron registros de proveedores activos o coincidentes.
                            </td>
                        </tr>
                        <tr v-for="provider in providers.items" :key="provider.id" class="admin-table-tr">
                            <td class="admin-table-td font-mono text-xs text-muted-foreground">
                                {{ provider.internal_code || 'N/A' }}
                            </td>
                            <td class="admin-table-td">
                                <div class="font-bold text-foreground">{{ provider.company_name }}</div>
                                <div v-if="provider.commercial_name" class="text-xs text-muted-foreground font-medium">{{ provider.commercial_name }}</div>
                            </td>
                            <td class="admin-table-td font-mono text-xs">
                                {{ provider.tax_id }}
                            </td>
                            <td class="admin-table-td">
                                <div class="text-sm font-medium" v-if="provider.contact_name">{{ provider.contact_name }}</div>
                                <div class="text-xs text-muted-foreground font-mono" v-if="provider.phone">{{ provider.phone }}</div>
                            </td>
                            <td class="admin-table-td font-mono text-xs text-center">
                                {{ provider.lead_time_days }}
                            </td>
                            <td class="admin-table-td font-mono text-xs text-right">
                                {{ Number(provider.min_order_value).toFixed(2) }}
                            </td>
                            <td class="admin-table-td text-center">
                                <span :class="provider.is_active ? 'badge-success' : 'badge-error'">
                                    {{ provider.is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="admin-table-td text-right">
                                <div class="inline-flex items-center gap-2">
                                    <Link
                                        :href="route('admin.operations.providers.edit', { provider: provider.id })"
                                        class="px-2 py-1 bg-secondary text-secondary-foreground text-xs font-semibold rounded border border-border hover:bg-neutral-200 dark:hover:bg-neutral-800 transition-colors"
                                    >
                                        Editar
                                    </Link>
                                    <button
                                        v-if="can_manage"
                                        type="button"
                                        @click="deleteProvider(provider.id)"
                                        class="px-2 py-1 bg-destructive/5 text-destructive border border-destructive/20 text-xs font-semibold rounded hover:bg-destructive/10 transition-colors"
                                    >
                                        Purgar
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="providers.meta.prev_cursor || providers.meta.next_cursor" class="flex items-center justify-end bg-card p-4 border border-border rounded-md shadow-flat gap-2">
                <Link
                    v-if="providers.meta.prev_cursor"
                    :href="route('admin.operations.providers.index')"
                    :data="{ cursor: providers.meta.prev_cursor, search: searchFilter || undefined }"
                    class="px-3 py-1.5 text-xs font-semibold rounded-md border border-border bg-card hover:bg-secondary transition-colors"
                >
                    Anteriores
                </Link>
                <div v-else class="px-3 py-1.5 text-xs text-muted-foreground/40 border border-border/50 bg-card rounded-md cursor-not-allowed select-none">
                    Anteriores
                </div>

                <Link
                    v-if="providers.meta.next_cursor"
                    :href="route('admin.operations.providers.index')"
                    :data="{ cursor: providers.meta.next_cursor, search: searchFilter || undefined }"
                    class="px-3 py-1.5 text-xs font-semibold rounded-md border border-border bg-card hover:bg-secondary transition-colors"
                >
                    Siguientes
                </Link>
                <div v-else class="px-3 py-1.5 text-xs text-muted-foreground/40 border border-border/50 bg-card rounded-md cursor-not-allowed select-none">
                    Siguientes
                </div>
            </div>
        </div>
    </AdminLayout>
</template>