<script setup>
import { ref } from 'react';
import { router, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    prices: {
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
 * Despacha la query de búsqueda hacia la acción del backend.
 */
const applyFilter = () => {
    router.get(route('admin.prices.index'), {
        search: searchFilter.value || undefined
    }, {
        preserveState: true,
        replace: true
    });
};

/**
 * Limpia los criterios de filtrado y refresca el pool de tarifas comerciales.
 */
const clearFilter = () => {
    searchFilter.value = '';
    applyFilter();
};

/**
 * Ejecuta la baja lógica por Soft Delete liberando la ventana temporal.
 */
const removePrice = (id) => {
    if (window.confirm('¿Está seguro de dar de baja esta tarifa comercial? Se liberará el segmento cronológico asignado.')) {
        router.delete(route('admin.prices.destroy', { price: id }), {
            preserveScroll: true
        });
    }
};
</script>

<template>
    <AdminLayout>
        <template #header>
            Matriz de Tarifas Comerciales
        </template>

        <div class="space-y-4">
            <div class="flex items-center gap-2 bg-card p-4 border border-border rounded-md shadow-flat max-w-md">
                <input 
                    v-model="searchFilter"
                    type="text"
                    placeholder="Buscar por SKU, producto o sucursal..."
                    class="admin-input"
                    @keyup.enter="applyFilter"
                />
                <button type="button" @click="applyFilter" class="px-3 py-2 bg-secondary text-secondary-foreground font-semibold rounded-md text-sm hover:bg-neutral-200 shrink-0">
                    Buscar
                </button>
                <button type="button" @click="clearFilter" class="px-3 py-2 bg-neutral-100 text-muted-foreground text-xs font-medium rounded-md border border-border hover:bg-neutral-200 shrink-0">
                    Limpiar
                </button>
                <Link :href="route('admin.prices.create')" class="admin-btn-primary inline-flex items-center gap-1.5 shrink-0 ml-2">
                    <span class="material-symbols-rounded text-lg">sell</span>
                    <span>Asignar Tarifa</span>
                </Link>
            </div>

            <div class="bg-card border border-border rounded-md shadow-flat overflow-x-auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th class="admin-table-th">Sucursal</th>
                            <th class="admin-table-th">Código SKU</th>
                            <th class="admin-table-th">Producto</th>
                            <th class="admin-table-th">Tipo Tarifa</th>
                            <th class="admin-table-th text-center">Cant. Mínima</th>
                            <th class="admin-table-th text-center">Prioridad</th>
                            <th class="admin-table-th text-right">Precio Lista</th>
                            <th class="admin-table-th text-right">Precio Final</th>
                            <th class="admin-table-th">Inicio Vigencia</th>
                            <th class="admin-table-th">Término Vigencia</th>
                            <th class="admin-table-th text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="prices.data.length === 0">
                            <td colspan="11" class="admin-table-td text-center text-muted-foreground py-6 font-normal">
                                No se encuentran estructuras tarifarias configuradas en el sistema.
                            </td>
                        </tr>
                        <tr v-for="item in prices.data" :key="item.id" class="admin-table-tr text-xs">
                            <td class="admin-table-td font-semibold text-foreground/80">
                                {{ item.branch_name }}
                            </td>
                            <td class="admin-table-td font-mono font-bold text-foreground">
                                {{ item.sku_code }}
                            </td>
                            <td class="admin-table-td text-muted-foreground font-medium">
                                {{ item.product_name }}
                            </td>
                            <td class="admin-table-td font-mono text-[11px] font-bold tracking-wider">
                                <span :class="{
                                    'text-blue-600': item.type === 'REGULAR',
                                    'text-purple-600': item.type === 'WHOLESALE',
                                    'text-amber-600': item.type === 'PROMOTION',
                                    'text-emerald-600': item.type === 'DISTRIBUTOR'
                                }">
                                    {{ item.type }}
                                </span>
                            </td>
                            <td class="admin-table-td text-center font-mono">
                                {{ item.min_quantity }} u.
                            </td>
                            <td class="admin-table-td text-center">
                                <span class="bg-neutral-100 font-mono text-foreground px-1.5 py-0.5 rounded border border-border">
                                    P-{{ item.priority }}
                                </span>
                            </td>
                            <td class="admin-table-td font-mono text-right text-muted-foreground">
                                {{ item.list_price.toFixed(2) }}
                            </td>
                            <td class="admin-table-td font-mono text-right font-bold text-foreground">
                                {{ item.final_price.toFixed(2) }}
                            </td>
                            <td class="admin-table-td font-mono text-muted-foreground">
                                {{ item.valid_from }}
                            </td>
                            <td class="admin-table-td font-mono" :class="item.valid_to.includes('∞') ? 'text-muted-foreground/40 italic' : 'text-muted-foreground'">
                                {{ item.valid_to }}
                            </td>
                            <td class="admin-table-td text-right">
                                <button 
                                    type="button" 
                                    @click="removePrice(item.id)"
                                    class="px-2 py-0.5 bg-destructive/5 text-destructive border border-destructive/15 text-[11px] font-semibold rounded hover:bg-destructive/10"
                                >
                                    Baja
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="prices.prev || prices.next" class="flex items-center justify-end bg-card p-4 border border-border rounded-md shadow-flat gap-2">
                <Link v-if="prices.prev" :href="route('admin.prices.index')" :data="{ cursor: prices.prev, search: searchFilter || undefined }" class="px-3 py-1.5 text-xs font-semibold rounded-md border border-border bg-card hover:bg-secondary transition-colors">
                    Anteriores
                </Link>
                <div v-else class="px-3 py-1.5 text-xs text-muted-foreground/40 border border-border/50 bg-card rounded-md cursor-not-allowed select-none">
                    Anteriores
                </div>

                <Link v-if="prices.next" :href="route('admin.prices.index')" :data="{ cursor: prices.next, search: searchFilter || undefined }" class="px-3 py-1.5 text-xs font-semibold rounded-md border border-border bg-card hover:bg-secondary transition-colors">
                    Siguientes
                </Link>
                <div v-else class="px-3 py-1.5 text-xs text-muted-foreground/40 border border-border/50 bg-card rounded-md cursor-not-allowed select-none">
                    Siguientes
                </div>
            </div>
        </div>
    </AdminLayout>
</template>