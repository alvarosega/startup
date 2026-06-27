<script setup>
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    balances: {
        type: Object,
        required: true
    },
    filters: {
        type: Object,
        required: true
    }
});

const searchFilter = ref(props.filters.search || '');

/**
 * Despacha los filtros de búsqueda por coincidencia de texto a la API de saldos.
 */
const applyFilter = () => {
    router.get(route('admin.inventory.index'), {
        search: searchFilter.value || undefined
    }, {
        preserveState: true,
        replace: true
    });
};

/**
 * Restablece los criterios de búsqueda de la bandeja general.
 */
const clearFilter = () => {
    searchFilter.value = '';
    applyFilter();
};
</script>

<template>
    <AdminLayout>
        <template #header>
            Balances Globales de Existencias
        </template>

        <div class="space-y-4">
            <div class="flex items-center gap-2 bg-card p-4 border border-border rounded-md shadow-flat max-w-md">
                <input 
                    v-model="searchFilter"
                    type="text"
                    placeholder="Buscar por SKU o variante de producto..."
                    class="admin-input"
                    @keyup.enter="applyFilter"
                />
                <button type="button" @click="applyFilter" class="px-3 py-2 bg-secondary text-secondary-foreground font-semibold rounded-md text-sm hover:bg-neutral-200 shrink-0">
                    Filtrar
                </button>
                <button type="button" @click="clearFilter" class="px-3 py-2 bg-neutral-100 text-muted-foreground text-xs font-medium rounded-md border border-border hover:bg-neutral-200 shrink-0">
                    Limpiar
                </button>
            </div>

            <div class="bg-card border border-border rounded-md shadow-flat overflow-x-auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th class="admin-table-th">Sucursal / Nodo</th>
                            <th class="admin-table-th">Código SKU</th>
                            <th class="admin-table-th">Descripción de Variante</th>
                            <th class="admin-table-th text-right">Físico Total</th>
                            <th class="admin-table-th text-right">Reservado</th>
                            <th class="admin-table-th text-right">Resguardo (Seguridad)</th>
                            <th class="admin-table-th text-right">Cuarentena</th>
                            <th class="admin-table-th text-right">Disponible Neto</th>
                            <th class="admin-table-th text-right">Trazabilidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="balances.data.length === 0">
                            <td colspan="9" class="admin-table-td text-center text-muted-foreground py-6 font-normal">
                                No se registran balances logísticos con los parámetros suministrados.
                            </td>
                        </tr>
                        <tr v-for="item in balances.data" :key="`${item.branch_id}-${item.sku_id}`" class="admin-table-tr">
                            <td class="admin-table-td font-semibold text-foreground">
                                {{ item.branch_name }}
                            </td>
                            <td class="admin-table-td font-mono text-xs font-bold text-foreground">
                                {{ item.sku_code }}
                            </td>
                            <td class="admin-table-td text-xs font-medium text-muted-foreground">
                                {{ item.product_name }}
                            </td>
                            <td class="admin-table-td font-mono text-xs text-right font-semibold">
                                {{ item.total_physical.toFixed(3) }}
                            </td>
                            <td class="admin-table-td font-mono text-xs text-right text-warning font-semibold">
                                {{ item.total_reserved.toFixed(3) }}
                            </td>
                            <td class="admin-table-td font-mono text-xs text-right text-info font-semibold">
                                {{ item.total_safety.toFixed(3) }}
                            </td>
                            <td class="admin-table-td font-mono text-xs text-right text-error font-semibold">
                                {{ item.total_quarantine.toFixed(3) }}
                            </td>
                            <td class="admin-table-td font-mono text-xs text-right font-black" :class="item.total_available > 0 ? 'text-success' : 'text-muted-foreground'">
                                {{ item.total_available.toFixed(3) }}
                            </td>
                            <td class="admin-table-td text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <Link 
                                        :href="route('admin.inventory.kardex', { skuId: item.sku_id })" 
                                        :data="{ branch_id: item.branch_id }"
                                        class="px-2 py-1 bg-secondary text-secondary-foreground text-xs font-semibold rounded border border-border hover:bg-neutral-200"
                                    >
                                        Kardex
                                    </Link>
                                    <Link 
                                        :href="route('admin.inventory.lots', { skuId: item.sku_id })" 
                                        :data="{ branch_id: item.branch_id }"
                                        class="px-2 py-1 bg-secondary text-secondary-foreground text-xs font-semibold rounded border border-border hover:bg-neutral-200"
                                    >
                                        Capas FIFO
                                    </Link>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="balances.prev || balances.next" class="flex items-center justify-end bg-card p-4 border border-border rounded-md shadow-flat gap-2">
                <Link v-if="balances.prev" :href="route('admin.inventory.index')" :data="{ cursor: balances.prev, search: searchFilter || undefined }" class="px-3 py-1.5 text-xs font-semibold rounded-md border border-border bg-card hover:bg-secondary transition-colors">
                    Anteriores
                </Link>
                <div v-else class="px-3 py-1.5 text-xs text-muted-foreground/40 border border-border/50 bg-card rounded-md cursor-not-allowed select-none">
                    Anteriores
                </div>

                <Link v-if="balances.next" :href="route('admin.inventory.index')" :data="{ cursor: balances.next, search: searchFilter || undefined }" class="px-3 py-1.5 text-xs font-semibold rounded-md border border-border bg-card hover:bg-secondary transition-colors">
                    Siguientes
                </Link>
                <div v-else class="px-3 py-1.5 text-xs text-muted-foreground/40 border border-border/50 bg-card rounded-md cursor-not-allowed select-none">
                    Siguientes
                </div>
            </div>
        </div>
    </AdminLayout>
</template>