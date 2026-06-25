<script setup>
import { ref, watch } from 'vue';
import { router, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    categories: {
        type: Object,
        required: true
    },
    parents: {
        type: Array,
        required: true
    },
    filters: {
        type: Object,
        required: true
    },
    skus: {
        type: Array,
        default: () => []
    },
    can_manage: {
        type: Boolean,
        required: true
    }
});

const searchFilter = ref(props.filters.search || '');
const expandedRows = ref([]);
const activeCategoryForSkus = ref(null);
const showSkuModal = ref(false);
const localSkus = ref([]);

// Formulario síncrono para despachar el reordenamiento de la góndola de SKUs
const orderForm = useForm({
    ids: []
});

/**
 * Conmuta la visibilidad de las subcategorías anidadas.
 */
const toggleRow = (id) => {
    if (expandedRows.value.includes(id)) {
        expandedRows.value = expandedRows.value.filter(rowId => rowId !== id);
    } else {
        expandedRows.value.push(id);
    }
};

/**
 * Ejecuta el filtrado por término de búsqueda.
 */
const applyFilter = () => {
    router.get(route('admin.catalog.categories.index'), {
        search: searchFilter.value || undefined
    }, {
        preserveState: true,
        replace: true
    });
};

/**
 * Limpia los criterios de búsqueda.
 */
const clearFilter = () => {
    searchFilter.value = '';
    applyFilter();
};

/**
 * Activa la recarga parcial de Inertia (Lazy Load) para recuperar los SKUs de la góndola.
 */
const openSkuReorder = (category) => {
    activeCategoryForSkus.value = category;
    router.reload({
        only: ['skus'],
        data: { selected_category: category.id },
        onSuccess: () => {
            localSkus.value = [...props.skus];
            showSkuModal.value = true;
        }
    });
};

/**
 * Modifica el orden de los elementos dentro del listado local de SKUs.
 */
const moveSku = (index, direction) => {
    const targetIndex = index + direction;
    if (targetIndex < 0 || targetIndex >= localSkus.value.length) return;
    
    const temp = localSkus.value[index];
    localSkus.value[index] = localSkus.value[targetIndex];
    localSkus.value[targetIndex] = temp;
};

/**
 * Transfiere el nuevo orden de la góndola al backend.
 */
const saveSkuOrder = () => {
    orderForm.ids = localSkus.value.map(sku => sku.id);
    orderForm.put(route('admin.catalog.categories.update-sku-order', { category: activeCategoryForSkus.value.id }), {
        onSuccess: () => {
            showSkuModal.value = false;
            activeCategoryForSkus.value = null;
            localSkus.value = [];
        }
    });
};

/**
 * Ejecuta la neutralización del nodo de categoría.
 */
const deleteCategory = (id) => {
    if (window.confirm('¿Está seguro de neutralizar esta categoría? Se validará la ausencia de dependencias.')) {
        router.delete(route('admin.catalog.categories.destroy', { category: id }), {
            preserveScroll: true
        });
    }
};
</script>

<template>
    <AdminLayout>
        <template #header>
            Matriz de Categorías y Góndolas
        </template>

        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-card p-4 border border-border rounded-md shadow-flat">
                <div class="flex items-center gap-2 max-w-md w-full">
                    <input 
                        v-model="searchFilter"
                        type="text"
                        placeholder="Buscar por nombre o código externo..."
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

                <div v-if="can_manage" class="shrink-0">
                    <Link :href="route('admin.catalog.categories.create')" class="admin-btn-primary inline-flex items-center gap-1.5">
                        <span class="material-symbols-rounded text-lg">create_new_folder</span>
                        <span>Nueva Categoría</span>
                    </Link>
                </div>
            </div>

            <div class="bg-card border border-border rounded-md shadow-flat overflow-x-auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th class="admin-table-th w-10"></th>
                            <th class="admin-table-th">Estructura / Categoría</th>
                            <th class="admin-table-th">Código Externo</th>
                            <th class="admin-table-th">Slug</th>
                            <th class="admin-table-th text-center">Orden</th>
                            <th class="admin-table-th text-center">Destacado</th>
                            <th class="admin-table-th text-center">Estado</th>
                            <th class="admin-table-th text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="categories.data.length === 0">
                            <td colspan="8" class="admin-table-td text-center text-muted-foreground py-6 font-normal">
                                No se encontraron categorías en el catálogo.
                            </td>
                        </tr>
                        <template v-for="category in categories.data" :key="category.id">
                            <tr class="admin-table-tr" :class="{ 'bg-neutral-50/60 font-medium': category.children?.length > 0 }">
                                <td class="admin-table-td text-center">
                                    <button 
                                        v-if="category.children && category.children.length > 0" 
                                        type="button" 
                                        @click="toggleRow(category.id)"
                                        class="p-1 rounded hover:bg-neutral-200 flex items-center justify-center mx-auto"
                                    >
                                        <span class="material-symbols-rounded text-lg transition-transform duration-100" :class="{ 'rotate-90': expandedRows.includes(category.id) }">
                                            chevron_right
                                        </span>
                                    </button>
                                </td>
                                <td class="admin-table-td">
                                    <div class="flex items-center gap-2">
                                        <span class="material-symbols-rounded text-muted-foreground text-lg">
                                            {{ category.parent_id ? 'folder_open' : 'folder' }}
                                        </span>
                                        <span>{{ category.name }}</span>
                                        <span v-if="category.parent" class="text-[10px] bg-neutral-200 px-1.5 py-0.5 rounded text-muted-foreground uppercase font-sans font-bold">
                                            Hija de: {{ category.parent.name }}
                                        </span>
                                    </div>
                                </td>
                                <td class="admin-table-td font-mono text-xs">{{ category.external_code || '—' }}</td>
                                <td class="admin-table-td font-mono text-xs text-muted-foreground">{{ category.slug }}</td>
                                <td class="admin-table-td text-center font-mono text-xs">{{ category.sort_order }}</td>
                                <td class="admin-table-td text-center">
                                    <span v-if="category.is_featured" class="badge-info">Destacado</span>
                                    <span v-else class="text-muted-foreground/30">—</span>
                                </td>
                                <td class="admin-table-td text-center">
                                    <span :class="category.is_active ? 'badge-success' : 'badge-error'">
                                        {{ category.is_active ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td class="admin-table-td text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        <button type="button" @click="openSkuReorder(category)" class="px-2 py-1 bg-secondary text-secondary-foreground text-xs font-semibold rounded border border-border hover:bg-neutral-200">
                                            Góndola
                                        </button>
                                        <Link :href="route('admin.catalog.categories.edit', { category: category.id })" class="px-2 py-1 bg-secondary text-secondary-foreground text-xs font-semibold rounded border border-border hover:bg-neutral-200">
                                            Configurar
                                        </Link>
                                        <button v-if="can_manage" type="button" @click="deleteCategory(category.id)" class="px-2 py-1 bg-destructive/5 text-destructive border border-destructive/20 text-xs font-semibold rounded hover:bg-destructive/10">
                                            Neutralizar
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr 
                                v-if="category.children && category.children.length > 0 && expandedRows.includes(category.id)"
                                v-for="child in category.children" 
                                :key="child.id"
                                class="bg-neutral-50/30 border-b border-border/40 hover:bg-neutral-100/40 transition-colors"
                            >
                                <td class="admin-table-td"></td>
                                <td class="admin-table-td pl-8">
                                    <div class="flex items-center gap-2">
                                        <span class="material-symbols-rounded text-muted-foreground/70 text-base">subheader</span>
                                        <span class="text-xs text-foreground font-medium">{{ child.name }}</span>
                                    </div>
                                </td>
                                <td class="admin-table-td font-mono text-xs text-muted-foreground">{{ child.external_code || '—' }}</td>
                                <td class="admin-table-td font-mono text-xs text-muted-foreground/60">{{ child.slug }}</td>
                                <td class="admin-table-td text-center font-mono text-xs text-muted-foreground">{{ child.sort_order }}</td>
                                <td class="admin-table-td text-center">
                                    <span v-if="child.is_featured" class="badge-info text-[10px]">Destacado</span>
                                    <span v-else class="text-muted-foreground/30">—</span>
                                end</td>
                                <td class="admin-table-td text-center">
                                    <span :class="child.is_active ? 'badge-success text-[10px]' : 'badge-error text-[10px]'">
                                        {{ child.is_active ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td class="admin-table-td text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        <button type="button" @click="openSkuReorder(child)" class="px-2 py-0.5 bg-card text-foreground text-xs font-medium rounded border border-border hover:bg-neutral-100">
                                            Góndola
                                        </button>
                                        <Link :href="route('admin.catalog.categories.edit', { category: child.id })" class="px-2 py-0.5 bg-card text-foreground text-xs font-medium rounded border border-border hover:bg-neutral-100">
                                            Configurar
                                        </Link>
                                        <button v-if="can_manage" type="button" @click="deleteCategory(child.id)" class="px-2 py-0.5 bg-destructive/5 text-destructive border border-destructive/15 text-xs font-medium rounded hover:bg-destructive/10">
                                            Neutralizar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <div v-if="showSkuModal" class="fixed inset-0 z-[200] flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-neutral-950/50 backdrop-blur-sm" @click="showSkuModal = false"></div>
            
            <div class="relative w-full max-w-lg bg-card border border-border rounded-md shadow-flat p-6 z-10 flex flex-col max-h-[85vh]">
                <div class="flex items-center justify-between mb-4 pb-2 border-b border-border shrink-0">
                    <h2 class="text-sm font-bold text-foreground uppercase tracking-wide">
                        Disposición en Góndola: {{ activeCategoryForSkus?.name }}
                    </h2>
                    <button type="button" @click="showSkuModal = false" class="p-1 rounded hover:bg-neutral-100 text-muted-foreground">
                        <span class="material-symbols-rounded text-base block">close</span>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto space-y-2 pr-1 no-scrollbar">
                    <p v-if="localSkus.length === 0" class="text-xs text-muted-foreground text-center py-6">
                        No existen SKUs vinculados a los productos de esta categoría.
                    </p>
                    <div 
                        v-for="(sku, index) in localSkus" 
                        :key="sku.id"
                        class="flex items-center justify-between p-2.5 bg-neutral-50 border border-border rounded-md text-xs font-medium"
                    >
                        <div class="space-y-0.5">
                            <div class="text-foreground font-bold">{{ sku.name }}</div>
                            <div class="text-[10px] text-muted-foreground font-mono">Producto: {{ sku.product?.name }}</div>
                        </div>
                        <div class="flex items-center gap-1 shrink-0">
                            <button type="button" @click="moveSku(index, -1)" :disabled="index === 0" class="p-1 bg-card border border-border rounded hover:bg-neutral-100 disabled:opacity-40">
                                <span class="material-symbols-rounded text-base block">arrow_upward</span>
                            </button>
                            <button type="button" @click="moveSku(index, 1)" :disabled="index === localSkus.length - 1" class="p-1 bg-card border border-border rounded hover:bg-neutral-100 disabled:opacity-40">
                                <span class="material-symbols-rounded text-base block">arrow_downward</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-border flex items-center justify-end gap-3 shrink-0 mt-4">
                    <button type="button" @click="showSkuModal = false" class="px-3 py-2 bg-secondary text-secondary-foreground font-semibold rounded-md text-sm hover:bg-neutral-200">
                        Cancelar
                    </button>
                    <button type="button" @click="saveSkuOrder" :disabled="orderForm.processing" class="admin-btn-primary inline-flex items-center gap-1.5">
                        <span>{{ orderForm.processing ? 'Actualizando Góndola...' : 'Guardar Disposición' }}</span>
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>