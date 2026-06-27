<script setup>
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    products: {
        type: Object,
        required: true
    },
    stats: {
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
    },
    options: {
        type: Object,
        required: true
    }
});

const searchFilter = ref(props.filters.search || '');
const categoryFilter = ref(props.filters.category || '');
const brandFilter = ref(props.filters.brand || '');
const statusFilter = ref(props.filters.status || '');
const expandedProducts = ref([]);

/**
 * Commuta la visibilidad inline de las variantes (SKUs) del producto maestro.
 */
const toggleProductSkus = (id) => {
    if (expandedProducts.value.includes(id)) {
        expandedProducts.value = expandedProducts.value.filter(prodId => rowId !== id);
    } else {
        expandedProducts.value.push(id);
    }
};

/**
 * Despacha los filtros de búsqueda y segmentación a través de cursores nativos.
 */
const applyFilters = () => {
    router.get(route('admin.catalog.products.index'), {
        search: searchFilter.value || undefined,
        category: categoryFilter.value || undefined,
        brand: brandFilter.value || undefined,
        status: statusFilter.value || undefined
    }, {
        preserveState: true,
        replace: true
    });
};

/**
 * Limpia los criterios de búsqueda de la consola de control.
 */
const clearFilters = () => {
    searchFilter.value = '';
    categoryFilter.value = '';
    brandFilter.value = '';
    statusFilter.value = '';
    applyFilters();
};

/**
 * Ejecuta la extracción física y lógica del producto maestro y sus variantes dependientes.
 */
const deleteProduct = (id) => {
    if (window.confirm('¿Desea eliminar este producto maestro? Se purgarán todas sus variantes físicas si no registran stock real.')) {
        router.delete(route('admin.catalog.products.destroy', { product: id }), {
            preserveScroll: true
        });
    }
};
</script>

<template>
    <AdminLayout>
        <template #header>
            Consola de Control del Catálogo
        </template>

        <div class="space-y-5">
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div class="bg-card border border-border p-4 rounded-md shadow-flat">
                    <span class="text-muted-foreground block text-[10px] uppercase font-bold tracking-wider">Productos Maestros</span>
                    <span class="text-2xl font-black text-foreground">{{ stats.total }}</span>
                </div>
                <div class="bg-card border border-border p-4 rounded-md shadow-flat">
                    <span class="text-muted-foreground block text-[10px] uppercase font-bold tracking-wider">Nodos Activos</span>
                    <span class="text-2xl font-black text-success">{{ stats.active }}</span>
                </div>
                <div class="bg-card border border-border p-4 rounded-md shadow-flat">
                    <span class="text-muted-foreground block text-[10px] uppercase font-bold tracking-wider">Variantes Comerciales (SKUs)</span>
                    <span class="text-2xl font-black text-info">{{ stats.total_skus }}</span>
                </div>
                <div class="bg-card border border-border p-4 rounded-md shadow-flat">
                    <span class="text-muted-foreground block text-[10px] uppercase font-bold tracking-wider">Productos Incompletos (Sin SKU)</span>
                    <span class="text-2xl font-black text-error">{{ stats.incomplete }}</span>
                </div>
            </div>

            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 bg-card p-4 border border-border rounded-md shadow-flat">
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 flex-1">
                    <div>
                        <input v-model="searchFilter" type="text" placeholder="Buscar por producto, EAN..." class="admin-input" @keyup.enter="applyFilters" />
                    </div>
                    <div>
                        <select v-model="categoryFilter" class="admin-input" @change="applyFilters">
                            <option value="">Todas las categorías</option>
                            <option v-for="c in options.categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                    </div>
                    <div>
                        <select v-model="brandFilter" class="admin-input" @change="applyFilters">
                            <option value="">Todas las marcas</option>
                            <option v-for="b in options.brands" :key="b.id" :value="b.id">{{ b.name }}</option>
                        </select>
                    </div>
                    <div>
                        <select v-model="statusFilter" class="admin-input" @change="applyFilters">
                            <option value="">Todos los estados de salud</option>
                            <option value="complete">Con variantes (Completos)</option>
                            <option value="incomplete">Sin variantes (Incompletos)</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <button type="button" @click="clearFilters" class="px-3 py-2 bg-secondary text-secondary-foreground font-semibold rounded-md text-sm hover:bg-neutral-200">
                        Limpiar
                    </button>
                    <Link :href="route('admin.catalog.products.reorder')" class="px-3 py-2 bg-secondary text-secondary-foreground font-semibold rounded-md text-sm hover:bg-neutral-200 inline-flex items-center gap-1.5">
                        <span class="material-symbols-rounded text-lg">low_priority</span>
                        <span>Priorizar</span>
                    </Link>
                    <Link v-if="can_manage" :href="route('admin.catalog.products.create')" class="admin-btn-primary inline-flex items-center gap-1.5">
                        <span class="material-symbols-rounded text-lg">add_box</span>
                        <span>Nuevo Producto</span>
                    </Link>
                </div>
            </div>

            <div class="bg-card border border-border rounded-md shadow-flat overflow-x-auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th class="admin-table-th w-10"></th>
                            <th class="admin-table-th w-14 text-center">Imagen</th>
                            <th class="admin-table-th">Producto Maestro</th>
                            <th class="admin-table-th">Marca</th>
                            <th class="admin-table-th">Categoría</th>
                            <th class="admin-table-th text-center">Variantes</th>
                            <th class="admin-table-th text-center">Atributos</th>
                            <th class="admin-table-th text-center">Estado</th>
                            <th class="admin-table-th text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="products.data.length === 0">
                            <td colspan="9" class="admin-table-td text-center text-muted-foreground py-6 font-normal">
                                No se encontraron productos maestros bajo los criterios aplicados.
                            </td>
                        </tr>
                        <template v-for="item in products.data" :key="item.id">
                            <tr class="admin-table-tr">
                                <td class="admin-table-td text-center">
                                    <button v-if="item.skus.length > 0" type="button" @click="toggleProductSkus(item.id)" class="p-1 rounded hover:bg-neutral-200 flex items-center justify-center mx-auto">
                                        <span class="material-symbols-rounded text-lg transition-transform duration-100" :class="{ 'rotate-90': expandedProducts.includes(item.id) }">
                                            chevron_right
                                        </span>
                                    </button>
                                </td>
                                <td class="admin-table-td text-center py-1">
                                    <div class="w-10 h-10 bg-neutral-100 border border-border rounded flex items-center justify-center overflow-hidden mx-auto">
                                        <img v-if="item.image_path" :src="`/storage/${item.image_path}`" class="w-full h-full object-contain" alt="" />
                                        <span v-else class="material-symbols-rounded text-muted-foreground/40 text-xl">image</span>
                                    </div>
                                </td>
                                <td class="admin-table-td">
                                    <div class="font-bold text-foreground">{{ item.name }}</div>
                                    <div class="text-[11px] text-muted-foreground font-mono font-normal tracking-tight">{{ item.slug }}</div>
                                </td>
                                <td class="admin-table-td font-semibold text-xs text-foreground/80">
                                    {{ item.brand?.name || 'N/A' }}
                                </td>
                                <td class="admin-table-td text-xs text-muted-foreground font-medium">
                                    {{ item.category?.name || 'N/A' }}
                                </td>
                                <td class="admin-table-td text-center">
                                    <span :class="item.skus_count > 0 ? 'badge-info' : 'badge-error'">
                                        {{ item.skus_count }} SKUs
                                    </span>
                                </td>
                                <td class="admin-table-td text-center space-x-1">
                                    <span v-if="item.is_featured" class="text-[10px] bg-amber-500/10 text-amber-600 font-bold px-1.5 py-0.5 rounded uppercase tracking-wide">Destacado</span>
                                    <span v-if="item.is_alcoholic" class="text-[10px] bg-purple-500/10 text-purple-600 font-bold px-1.5 py-0.5 rounded uppercase tracking-wide">Alcohol</span>
                                </td>
                                <td class="admin-table-td text-center">
                                    <span :class="item.is_active ? 'badge-success' : 'badge-error'">
                                        {{ item.is_active ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td class="admin-table-td text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        <Link :href="route('admin.catalog.products.edit', { product: item.id })" class="px-2 py-1 bg-secondary text-secondary-foreground text-xs font-semibold rounded border border-border hover:bg-neutral-200">
                                            Workspace
                                        </Link>
                                        <button v-if="can_manage" type="button" @click="deleteProduct(item.id)" class="px-2 py-1 bg-destructive/5 text-destructive border border-destructive/20 text-xs font-semibold rounded hover:bg-destructive/10">
                                            Purgar
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="item.skus.length > 0 && expandedProducts.includes(item.id)" class="bg-neutral-50/50 border-b border-border/40 animate-in fade-in duration-75">
                                <td class="admin-table-td"></td>
                                <td colspan="8" class="p-3 pl-4">
                                    <div class="border border-border/60 rounded overflow-hidden shadow-sm bg-card">
                                        <table class="w-full text-left border-collapse">
                                            <thead>
                                                <tr class="bg-secondary/40 border-b border-border/60">
                                                    <th class="px-3 py-1.5 text-[11px] font-bold text-muted-foreground uppercase tracking-wider">Código EAN</th>
                                                    <th class="px-3 py-1.5 text-[11px] font-bold text-muted-foreground uppercase tracking-wider">Variante Comercial</th>
                                                    <th class="px-3 py-1.5 text-[11px] font-bold text-muted-foreground uppercase tracking-wider text-right">Precio Base</th>
                                                    <th class="px-3 py-1.5 text-[11px] font-bold text-muted-foreground uppercase tracking-wider text-center">Factor Conversión</th>
                                                    <th class="px-3 py-1.5 text-[11px] font-bold text-muted-foreground uppercase tracking-wider text-center">Peso (Kg)</th>
                                                    <th class="px-3 py-1.5 text-[11px] font-bold text-muted-foreground uppercase tracking-wider text-center">Estado</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="sku in item.skus" :key="sku.id" class="border-b border-border/30 last:border-b-0 hover:bg-neutral-50/80 transition-colors">
                                                    <td class="px-3 py-2 font-mono text-xs text-foreground font-semibold tracking-tight">{{ sku.code }}</td>
                                                    <td class="px-3 py-2 text-xs text-foreground font-medium">{{ sku.name }}</td>
                                                    <td class="px-3 py-2 font-mono text-xs text-right text-foreground font-bold">{{ sku.base_price.toFixed(2) }}</td>
                                                    <td class="px-3 py-2 font-mono text-xs text-center text-muted-foreground">{{ sku.conversion_factor.toFixed(3) }}</td>
                                                    <td class="px-3 py-2 font-mono text-xs text-center text-muted-foreground">{{ sku.weight.toFixed(3) }}</td>
                                                    <td class="px-3 py-2 text-center">
                                                        <span :class="sku.is_active ? 'text-success font-bold bg-success/5 px-1 rounded text-[10px]' : 'text-error font-bold bg-error/5 px-1 rounded text-[10px]'">
                                                            {{ sku.is_active ? 'OPERATIVO' : 'SUSPENDIDO' }}
                                                        </span>
                                                    </td>
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

            <div v-if="prev || next" class="flex items-center justify-end bg-card p-4 border border-border rounded-md shadow-flat gap-2">
                <Link v-if="prev" :href="route('admin.catalog.products.index')" :data="{ cursor: prev, search: searchFilter || undefined, category: categoryFilter || undefined, brand: brandFilter || undefined, status: statusFilter || undefined }" class="px-3 py-1.5 text-xs font-semibold rounded-md border border-border bg-card hover:bg-secondary transition-colors">
                    Anteriores
                </Link>
                <div v-else class="px-3 py-1.5 text-xs text-muted-foreground/40 border border-border/50 bg-card rounded-md cursor-not-allowed select-none">
                    Anteriores
                </div>

                <Link v-if="next" :href="route('admin.catalog.products.index')" :data="{ cursor: next, search: searchFilter || undefined, category: categoryFilter || undefined, brand: brandFilter || undefined, status: statusFilter || undefined }" class="px-3 py-1.5 text-xs font-semibold rounded-md border border-border bg-card hover:bg-secondary transition-colors">
                    Siguientes
                </Link>
                <div v-else class="px-3 py-1.5 text-xs text-muted-foreground/40 border border-border/50 bg-card rounded-md cursor-not-allowed select-none">
                    Siguientes
                </div>
            </div>
        </div>
    </AdminLayout>
</template>