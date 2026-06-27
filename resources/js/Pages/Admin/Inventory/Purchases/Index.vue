<script setup>
import { ref, computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    purchases: {
        type: Object,
        required: true
    },
    filters: {
        type: Object,
        required: true
    }
});

const searchFilter = ref(props.filters.search || '');
const showAuditRecords = ref(false);

/**
 * Filtra localmente la colección basándose en el estado del interruptor de auditoría.
 */
const filteredPurchases = computed(() => {
    if (showAuditRecords.value) {
        return props.purchases.data;
    }
    return props.purchases.data.filter(p => !p.is_deleted && p.status !== 'CANCELLED');
});

/**
 * Despacha la consulta por término de búsqueda preservando la consistencia de la SPA.
 */
const applyFilter = () => {
    router.get(route('admin.purchases.index'), {
        search: searchFilter.value || undefined
    }, {
        preserveState: true,
        replace: true
    });
};

/**
 * Restablece los filtros del listado de abastecimiento.
 */
const clearFilter = () => {
    searchFilter.value = '';
    applyFilter();
};

/**
 * Envía la petición síncrona POST para revocar y aplicar el soft-delete inmediato.
 */
const cancelPurchase = (id) => {
    if (window.confirm('¿Está seguro de cancelar esta orden de compra pendiente? Se purgará de los flujos de trabajo activos.')) {
        router.post(route('admin.purchases.cancel', { purchase: id }), {}, {
            preserveScroll: true
        });
    }
};
</script>

<template>
    <AdminLayout>
        <template #header>
            Historial de Abastecimiento de Inventario
        </template>

        <div class="space-y-4">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 bg-card p-4 border border-border rounded-md shadow-flat">
                <div class="flex flex-col sm:flex-row sm:items-center gap-4 flex-1">
                    <div class="flex items-center gap-2 max-w-xs w-full">
                        <input 
                            v-model="searchFilter"
                            type="text"
                            placeholder="Buscar por nro. documento..."
                            class="admin-input"
                            @keyup.enter="applyFilter"
                        />
                        <button type="button" @click="applyFilter" class="px-3 py-2 bg-secondary text-secondary-foreground font-semibold rounded-md text-sm hover:bg-neutral-200">
                            Buscar
                        </button>
                        <button type="button" @click="clearFilter" class="p-2 text-muted-foreground hover:text-foreground">
                            <span class="material-symbols-rounded text-lg block">backspace</span>
                        </button>
                    </div>

                    <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                        <input 
                            v-model="showAuditRecords" 
                            type="checkbox" 
                            class="rounded border-input text-primary focus:ring-ring bg-card w-4 h-4" 
                        />
                        <span class="text-xs font-bold text-foreground uppercase tracking-wide">
                            Mostrar Cancelados y Archivados
                        </span>
                    </label>
                </div>

                <div class="shrink-0">
                    <Link :href="route('admin.purchases.create')" class="admin-btn-primary inline-flex items-center gap-1.5">
                        <span class="material-symbols-rounded text-lg">unarchive</span>
                        <span>Registrar Compra</span>
                    </Link>
                </div>
            </div>

            <div class="bg-card border border-border rounded-md shadow-flat overflow-x-auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th class="admin-table-th">Documento</th>
                            <th class="admin-table-th">Fecha Compra</th>
                            <th class="admin-table-th">Sucursal Destino</th>
                            <th class="admin-table-th">Proveedor</th>
                            <th class="admin-table-th">Operador Admin</th>
                            <th class="admin-table-th">Término</th>
                            <th class="admin-table-th text-center">Estado</th>
                            <th class="admin-table-th text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="filteredPurchases.length === 0">
                            <td colspan="8" class="admin-table-td text-center text-muted-foreground py-6 font-normal">
                                No se encontraron asientos de compra bajo los criterios activos.
                            </td>
                        </tr>
                        <tr 
                            v-for="item in filteredPurchases" 
                            :key="item.id" 
                            class="admin-table-tr"
                            :class="{ 'opacity-50 bg-neutral-50 dark:bg-neutral-900/40 select-none': item.is_deleted || item.status === 'CANCELLED' }"
                        >
                            <td class="admin-table-td font-mono text-xs font-bold text-foreground">
                                {{ item.document_number }}
                            </td>
                            <td class="admin-table-td font-mono text-xs">
                                {{ item.purchase_date }}
                            </td>
                            <td class="admin-table-td">
                                {{ item.branch_name || 'N/A' }}
                            </td>
                            <td class="admin-table-td">
                                {{ item.provider_name || 'N/A' }}
                            </td>
                            <td class="admin-table-td text-muted-foreground text-xs">
                                {{ item.admin_name || 'N/A' }}
                            </td>
                            <td class="admin-table-td font-mono text-xs">
                                <span :class="item.payment_type === 'CASH' ? 'text-success font-semibold' : 'text-amber-600 font-semibold'">
                                    {{ item.payment_type }}
                                </span>
                            </td>
                            <td class="admin-table-td text-center">
                                <span v-if="item.status === 'COMPLETED'" class="badge-success">CONSOLIDADO</span>
                                <span v-else-if="item.status === 'PENDING'" class="badge-info">PENDIENTE</span>
                                <span v-else class="badge-error">CANCELADO</span>
                            </td>
                            <td class="admin-table-td text-right">
                                <div v-if="!item.is_deleted && item.status === 'PENDING'" class="inline-flex items-center gap-1.5">
                                    <Link 
                                        :href="route('admin.purchases.edit', { purchase: item.id })" 
                                        class="px-2 py-1 bg-primary text-primary-foreground text-xs font-semibold rounded hover:bg-primary/90 transition-colors"
                                    >
                                        Consolidar
                                    </Link>
                                    <button 
                                        type="button" 
                                        @click="cancelPurchase(item.id)" 
                                        class="px-2 py-1 bg-destructive/5 text-destructive border border-destructive/20 text-xs font-semibold rounded hover:bg-destructive/10 transition-colors"
                                    >
                                        Cancelar
                                    </button>
                                </div>
                                <span v-else class="text-xs text-muted-foreground/50 italic pr-2">Histórico</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="purchases.prev || purchases.next" class="flex items-center justify-end bg-card p-4 border border-border rounded-md shadow-flat gap-2">
                <Link
                    v-if="purchases.prev"
                    :href="route('admin.purchases.index')"
                    :data="{ cursor: purchases.prev, search: searchFilter || undefined }"
                    class="px-3 py-1.5 text-xs font-semibold rounded-md border border-border bg-card hover:bg-secondary transition-colors"
                >
                    Anteriores
                </Link>
                <div v-else class="px-3 py-1.5 text-xs text-muted-foreground/40 border border-border/50 bg-card rounded-md cursor-not-allowed select-none">
                    Anteriores
                </div>

                <Link
                    v-if="purchases.next"
                    :href="route('admin.purchases.index')"
                    :data="{ cursor: purchases.next, search: searchFilter || undefined }"
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