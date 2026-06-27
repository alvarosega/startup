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
const showCancelledAndDeleted = ref(false);

/**
 * Filtra el listado local basándose en el estado de revocación u ocultamiento lógico.
 */
const filteredPurchases = computed(() => {
    return props.purchases.data.filter(purchase => {
        if (!showCancelledAndDeleted.value) {
            return purchase.status !== 'CANCELLED' && !purchase.is_deleted;
        }
        return true;
    });
});

/**
 * Calcula el monto total agregado del documento en el cliente.
 */
const calculatePurchaseTotal = (items) => {
    return items.reduce((sum, item) => sum + (item.quantity * item.cost_price), 0);
};

/**
 * Despacha el criterio de búsqueda por texto hacia el listado del servidor.
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
 * Limpia los filtros y reestablece la bandeja ordinaria.
 */
const clearFilter = () => {
    searchFilter.value = '';
    applyFilter();
};

/**
 * Despacha la revocación atómica de una orden de compra pendiente.
 */
const cancelPurchase = (id) => {
    if (window.confirm('¿Está seguro de revocar esta orden de compra? Se aplicará un soft delete y quedará fuera de los flujos activos.')) {
        router.post(route('admin.purchases.cancel', { purchase: id }), {}, {
            preserveScroll: true
        });
    }
};
</script>

<template>
    <AdminLayout>
        <template #header>
            Bandeja de Órdenes de Compra y Abastecimiento
        </template>

        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-card p-4 border border-border rounded-md shadow-flat">
                <div class="flex flex-wrap items-center gap-4 flex-1">
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
                        <button type="button" @click="clearFilter" class="px-3 py-2 bg-neutral-100 text-muted-foreground text-xs font-medium rounded-md border border-border hover:bg-neutral-200">
                            Limpiar
                        </button>
                    </div>

                    <label class="inline-flex items-center gap-2 cursor-pointer select-none border-l border-border pl-4 h-9">
                        <input 
                            v-model="showCancelledAndDeleted"
                            type="checkbox" 
                            class="rounded border-input text-primary focus:ring-ring bg-card w-4 h-4 transition-colors"
                        />
                        <span class="text-xs font-bold text-muted-foreground uppercase tracking-wide">
                            Mostrar Órdenes Canceladas / Purgadas
                        </span>
                    </label>
                </div>

                <div class="shrink-0">
                    <Link :href="route('admin.purchases.create')" class="admin-btn-primary inline-flex items-center gap-1.5">
                        <span class="material-symbols-rounded text-lg">local_shipping</span>
                        <span>Registrar Ingreso</span>
                    </Link>
                </div>
            </div>

            <div class="bg-card border border-border rounded-md shadow-flat overflow-x-auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th class="admin-table-th">Nro. Documento</th>
                            <th class="admin-table-th">Fecha de Compra</th>
                            <th class="admin-table-th">Sucursal Destino</th>
                            <th class="admin-table-th">Socio Comercial (Proveedor)</th>
                            <th class="admin-table-th">Comprador (Admin)</th>
                            <th class="admin-table-th text-center">Ítems</th>
                            <th class="admin-table-th text-right">Monto Total</th>
                            <th class="admin-table-th text-center">Estado</th>
                            <th class="admin-table-th text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="filteredPurchases.length === 0">
                            <td colspan="9" class="admin-table-td text-center text-muted-foreground py-6 font-normal">
                                No se encontraron asientos de compra bajo los parámetros de visualización activos.
                            </td>
                        </tr>
                        <tr 
                            v-for="item in filteredPurchases" 
                            :key="item.id" 
                            class="admin-table-tr"
                            :class="{ 'opacity-50 bg-neutral-50/40 select-none': item.is_deleted || item.status === 'CANCELLED' }"
                        >
                            <td class="admin-table-td font-mono text-xs font-bold text-foreground">
                                {{ item.document_number }}
                            </td>
                            <td class="admin-table-td font-mono text-xs whitespace-nowrap">
                                {{ item.purchase_date }}
                            </td>
                            <td class="admin-table-td font-semibold text-xs text-foreground/80">
                                {{ item.branch_name }}
                            </td>
                            <td class="admin-table-td text-xs text-foreground/80 font-medium">
                                {{ item.provider_name }}
                            </td>
                            <td class="admin-table-td text-xs text-muted-foreground">
                                {{ item.admin_name }}
                            </td>
                            <td class="admin-table-td text-center font-mono text-xs">
                                {{ item.items.length }}
                            </td>
                            <td class="admin-table-td font-mono text-xs text-right font-bold text-foreground">
                                {{ calculatePurchaseTotal(item.items).toFixed(2) }}
                            </td>
                            <td class="admin-table-td text-center whitespace-nowrap">
                                <span v-if="item.status === 'COMPLETED'" class="badge-success">Consolidado</span>
                                <span v-else-if="item.status === 'PENDING'" class="badge-warning">Documental</span>
                                <span v-else-if="item.status === 'CANCELLED'" class="badge-error">Revocado</span>
                            </td>
                            <td class="admin-table-td text-right">
                                <div v-if="item.status === 'PENDING' && !item.is_deleted" class="inline-flex items-center gap-1.5">
                                    <Link 
                                        :href="route('admin.purchases.edit', { purchase: item.id })"
                                        class="px-2 py-1 bg-secondary text-secondary-foreground text-xs font-semibold rounded border border-border hover:bg-neutral-200 whitespace-nowrap"
                                    >
                                        Consolidar Carga
                                    </Link>
                                    <button 
                                        type="button"
                                        @click="cancelPurchase(item.id)"
                                        class="px-2 py-1 bg-destructive/5 text-destructive border border-destructive/20 text-xs font-semibold rounded hover:bg-destructive/10"
                                    >
                                        Revocar
                                    </button>
                                </div>
                                <span v-else class="text-muted-foreground/30 text-xs font-mono select-none pr-2">—</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="purchases.prev || purchases.next" class="flex items-center justify-end bg-card p-4 border border-border rounded-md shadow-flat gap-2">
                <Link v-if="purchases.prev" :href="route('admin.purchases.index')" :data="{ cursor: purchases.prev, search: searchFilter || undefined }" class="px-3 py-1.5 text-xs font-semibold rounded-md border border-border bg-card hover:bg-secondary transition-colors">
                    Anteriores
                </Link>
                <div v-else class="px-3 py-1.5 text-xs text-muted-foreground/40 border border-border/50 bg-card rounded-md cursor-not-allowed select-none">
                    Anteriores
                </div>

                <Link v-if="purchases.next" :href="route('admin.purchases.index')" :data="{ cursor: purchases.next, search: searchFilter || undefined }" class="px-3 py-1.5 text-xs font-semibold rounded-md border border-border bg-card hover:bg-secondary transition-colors">
                    Siguientes
                </Link>
                <div v-else class="px-3 py-1.5 text-xs text-muted-foreground/40 border border-border/50 bg-card rounded-md cursor-not-allowed select-none">
                    Siguientes
                </div>
            </div>
        </div>
    </AdminLayout>
</template>