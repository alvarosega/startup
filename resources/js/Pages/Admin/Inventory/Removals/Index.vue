<script setup>
import { ref, computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    removals: {
        type: Object,
        required: true
    },
    filters: {
        type: Object,
        required: true
    }
});

const searchFilter = ref(props.filters.search || '');
const expandedRows = ref([]);

/**
 * Conmuta la visibilidad del desglose de artículos afectados por la merma.
 */
const toggleRow = (id) => {
    if (expandedRows.value.includes(id)) {
        expandedRows.value = expandedRows.value.filter(rowId => rowId !== id);
    } else {
        expandedRows.value.push(id);
    }
};

/**
 * Calcula de forma agregada el costo total de la pérdida por solicitud.
 */
const calculateRemovalTotal = (items) => {
    return items.reduce((sum, item) => sum + item.total_loss, 0);
};

/**
 * Despacha el criterio de búsqueda por texto hacia el servidor.
 */
const applyFilter = () => {
    router.get(route('admin.removals.index'), {
        search: searchFilter.value || undefined
    }, {
        preserveState: true,
        replace: true
    });
};

/**
 * Purga el filtro de búsqueda.
 */
const clearFilter = () => {
    searchFilter.value = '';
    applyFilter();
};
</script>

<template>
    <AdminLayout>
        <template #header>
            Historial Contable de Mermas y Bajas
        </template>

        <div class="space-y-4">
            <div class="flex items-center gap-2 bg-card p-4 border border-border rounded-md shadow-flat max-w-md">
                <input 
                    v-model="searchFilter"
                    type="text"
                    placeholder="Buscar por código de baja..."
                    class="admin-input"
                    @keyup.enter="applyFilter"
                />
                <button type="button" @click="applyFilter" class="px-3 py-2 bg-secondary text-secondary-foreground font-semibold rounded-md text-sm hover:bg-neutral-200 shrink-0">
                    Buscar
                </button>
                <button type="button" @click="clearFilter" class="px-3 py-2 bg-neutral-100 text-muted-foreground text-xs font-medium rounded-md border border-border hover:bg-neutral-200 shrink-0">
                    Limpiar
                </button>
                <Link :href="route('admin.removals.create')" class="admin-btn-primary inline-flex items-center gap-1.5 shrink-0 ml-2">
                    <span class="material-symbols-rounded text-lg">delete_sweep</span>
                    <span>Declarar Baja</span>
                </Link>
            </div>

            <div class="bg-card border border-border rounded-md shadow-flat overflow-x-auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th class="admin-table-th w-10"></th>
                            <th class="admin-table-th">Código de Acta</th>
                            <th class="admin-table-th">Fecha / Hora</th>
                            <th class="admin-table-th">Sucursal Origen</th>
                            <th class="admin-table-th">Motivo de Salida</th>
                            <th class="admin-table-th">Operador Responsable</th>
                            <th class="admin-table-th text-right">Costo Total Pérdida</th>
                            <th class="admin-table-th">Notas de Justificación</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="removals.data.length === 0">
                            <td colspan="8" class="admin-table-td text-center text-muted-foreground py-6 font-normal">
                                No se registran declaraciones de merma en el sistema de auditoría.
                            </td>
                        </tr>
                        <template v-for="removal in removals.data" :key="removal.id">
                            <tr class="admin-table-tr">
                                <td class="admin-table-td text-center">
                                    <button 
                                        type="button" 
                                        @click="toggleRow(removal.id)"
                                        class="p-1 rounded hover:bg-neutral-200 flex items-center justify-center mx-auto"
                                    >
                                        <span class="material-symbols-rounded text-lg transition-transform duration-100" :class="{ 'rotate-90': expandedRows.includes(removal.id) }">
                                            chevron_right
                                        </span>
                                    </button>
                                </td>
                                <td class="admin-table-td font-mono text-xs font-bold text-foreground">
                                    {{ removal.code }}
                                </td>
                                <td class="admin-table-td font-mono text-xs text-muted-foreground whitespace-nowrap">
                                    {{ removal.approved_at }}
                                </td>
                                <td class="admin-table-td font-semibold text-xs text-foreground/80">
                                    {{ removal.branch_name }}
                                </td>
                                <td class="admin-table-td font-bold text-[11px] tracking-wide uppercase">
                                    <span :class="{
                                        'text-red-600': removal.reason === 'expiration',
                                        'text-amber-600': removal.reason === 'damage',
                                        'text-purple-600': removal.reason === 'theft',
                                        'text-blue-600': removal.reason === 'internal_use',
                                        'text-neutral-500': removal.reason === 'admin_error'
                                    }">
                                        {{ removal.reason }}
                                    </span>
                                </td>
                                <td class="admin-table-td text-xs text-foreground font-medium whitespace-nowrap">
                                    {{ removal.admin_name }}
                                </td>
                                <td class="admin-table-td font-mono text-xs text-right font-black text-destructive">
                                    {{ calculateRemovalTotal(removal.items).toFixed(2) }}
                                </td>
                                <td class="admin-table-td text-xs text-muted-foreground max-w-xs truncate italic">
                                    {{ removal.notes || 'Sin observaciones registradas.' }}
                                </td>
                            </tr>

                            <tr v-if="expandedRows.includes(removal.id)" class="bg-neutral-50/60 border-b border-border/40">
                                <td class="admin-table-td"></td>
                                <td colspan="7" class="p-3 pl-4">
                                    <div class="border border-border/60 rounded overflow-hidden bg-card shadow-sm">
                                        <table class="w-full text-left border-collapse">
                                            <thead>
                                                <tr class="bg-neutral-50 border-b border-border/60">
                                                    <th class="px-3 py-1.5 text-[10px] font-bold text-muted-foreground uppercase tracking-wider">Lote Descontado</th>
                                                    <th class="px-3 py-1.5 text-[10px] font-bold text-muted-foreground uppercase tracking-wider">Código SKU</th>
                                                    <th class="px-3 py-1.5 text-[10px] font-bold text-muted-foreground uppercase tracking-wider">Descripción de Producto</th>
                                                    <th class="px-3 py-1.5 text-[10px] font-bold text-muted-foreground uppercase tracking-wider text-right">Cantidad Extraída</th>
                                                    <th class="px-3 py-1.5 text-[10px] font-bold text-muted-foreground uppercase tracking-wider text-right">Costo Declarado</th>
                                                    <th class="px-3 py-1.5 text-[10px] font-bold text-muted-foreground uppercase tracking-wider text-right">Subtotal Pérdida</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="subItem in removal.items" :key="subItem.id" class="border-b border-border/30 last:border-b-0">
                                                    <td class="px-3 py-2 font-mono text-xs text-foreground font-semibold">{{ subItem.lot_code }}</td>
                                                    <td class="px-3 py-2 font-mono text-xs text-muted-foreground">{{ subItem.sku_code }}</td>
                                                    <td class="px-3 py-2 text-xs text-foreground font-medium">{{ subItem.product_name }}</td>
                                                    <td class="px-3 py-2 font-mono text-xs text-right text-foreground font-semibold">{{ subItem.quantity.toFixed(3) }}</td>
                                                    <td class="px-3 py-2 font-mono text-xs text-right text-muted-foreground">{{ subItem.unit_cost.toFixed(2) }}</td>
                                                    <td class="px-3 py-2 font-mono text-xs text-right font-bold text-destructive/80">{{ subItem.total_loss.toFixed(2) }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div v-if="removals.prev || removals.next" class="flex items-center justify-end bg-card p-4 border border-border rounded-md shadow-flat gap-2">
                <Link v-if="removals.prev" :href="route('admin.removals.index')" :data="{ cursor: removals.prev, search: searchFilter || undefined }" class="px-3 py-1.5 text-xs font-semibold rounded-md border border-border bg-card hover:bg-secondary transition-colors">
                    Anteriores
                </Link>
                <div v-else class="px-3 py-1.5 text-xs text-muted-foreground/40 border border-border/50 bg-card rounded-md cursor-not-allowed select-none">
                    Anteriores
                </div>

                <Link v-if="removals.next" :href="route('admin.removals.index')" :data="{ cursor: removals.next, search: searchFilter || undefined }" class="px-3 py-1.5 text-xs font-semibold rounded-md border border-border bg-card hover:bg-secondary transition-colors">
                    Siguientes
                </Link>
                <div v-else class="px-3 py-1.5 text-xs text-muted-foreground/40 border border-border/50 bg-card rounded-md cursor-not-allowed select-none">
                    Siguientes
                </div>
            </div>
        </div>
    </AdminLayout>
</template>