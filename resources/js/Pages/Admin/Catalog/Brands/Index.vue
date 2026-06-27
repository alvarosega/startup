<script setup>
import { ref, watch } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    brands: {
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
    }
});

const searchFilter = ref(props.filters.search || '');
const providerFilter = ref(props.filters.provider_id || '');
const categoryFilter = ref(props.filters.category_id || '');

/**
 * Despacha el filtrado de marcas al backend manteniendo el estado de los inputs.
 */
const applyFilters = () => {
    router.get(route('admin.catalog.brands.index'), {
        search: searchFilter.value || undefined,
        provider_id: providerFilter.value || undefined,
        category_id: categoryFilter.value || undefined
    }, {
        preserveState: true,
        replace: true
    });
};

/**
 * Purga todos los filtros locales y reestablece el listado.
 */
const clearFilters = () => {
    searchFilter.value = '';
    providerFilter.value = '';
    categoryFilter.value = '';
    applyFilters();
};

// Observadores para filtrado automático inmediato ante mutación de selectores
watch([providerFilter, categoryFilter], () => {
    applyFilters();
});

/**
 * Ejecuta la neutralización de la marca respetando las restricciones de integridad.
 */
const deleteBrand = (id) => {
    if (window.confirm('¿Está seguro de neutralizar esta marca del catálogo operacional?')) {
        router.delete(route('admin.catalog.brands.destroy', { brand: id }), {
            preserveScroll: true
        });
    }
};
</script>

<template>
    <AdminLayout>
        <template #header>
            Catálogo de Marcas
        </template>

        <div class="space-y-4">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 bg-card p-4 border border-border rounded-md shadow-flat">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 flex-1">
                    <div>
                        <input 
                            v-model="searchFilter"
                            type="text"
                            placeholder="Buscar marca..."
                            class="admin-input"
                            @keyup.enter="applyFilters"
                        />
                    </div>
                    <div>
                        <select v-model="providerFilter" class="admin-input">
                            <option value="">Todos los proveedores</option>
                            <option 
                                v-for="item in Array.from(new Set(brands.data.map(b => JSON.stringify(b.provider)).filter(Boolean))).map(s => JSON.parse(s))" 
                                :key="item.id" 
                                :value="item.id"
                            >
                                {{ item.commercial_name }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <select v-model="categoryFilter" class="admin-input">
                            <option value="">Todas las categorías</option>
                            <option 
                                v-for="item in Array.from(new Set(brands.data.map(b => JSON.stringify(b.category)).filter(Boolean))).map(s => JSON.parse(s))" 
                                :key="item.id" 
                                :value="item.id"
                            >
                                {{ item.name }}
                            </option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <button type="button" @click="clearFilters" class="px-3 py-2 bg-neutral-100 text-muted-foreground text-xs font-medium rounded-md border border-border hover:bg-neutral-200">
                        Limpiar Filtros
                    </button>
                    <Link 
                        v-if="can_manage"
                        :href="route('admin.catalog.brands.create')"
                        class="admin-btn-primary inline-flex items-center gap-1.5"
                    >
                        <span class="material-symbols-rounded text-lg">branding_watermark</span>
                        <span>Nueva Marca</span>
                    </Link>
                </div>
            </div>

            <div class="bg-card border border-border rounded-md shadow-flat overflow-x-auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th class="admin-table-th w-16 text-center">Imagen</th>
                            <th class="admin-table-th">Nombre de Marca</th>
                            <th class="admin-table-th">Socio Comercial (Proveedor)</th>
                            <th class="admin-table-th">Categoría Raíz</th>
                            <th class="admin-table-th text-center">Orden</th>
                            <th class="admin-table-th text-center">Destacado</th>
                            <th class="admin-table-th text-center">Estado</th>
                            <th class="admin-table-th text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="brands.data.length === 0">
                            <td colspan="8" class="admin-table-td text-center text-muted-foreground py-6 font-normal">
                                No se localizaron marcas bajo los criterios de búsqueda especificados.
                            </td>
                        </tr>
                        <tr v-for="item in brands.data" :key="item.id" class="admin-table-tr">
                            <td class="admin-table-td text-center py-1">
                                <div class="w-10 h-10 bg-neutral-100 border border-border rounded flex items-center justify-center overflow-hidden mx-auto">
                                    <img v-if="item.image_path" :src="`/storage/${item.image_path}`" class="w-full h-full object-contain" alt="" />
                                    <span v-else class="material-symbols-rounded text-muted-foreground/40 text-xl">image</span>
                                </div>
                            </td>
                            <td class="admin-table-td font-bold text-foreground">
                                <div>{{ item.name }}</div>
                                <div class="text-[11px] text-muted-foreground font-mono font-normal tracking-tight">{{ item.slug }}</div>
                            </td>
                            <td class="admin-table-td">
                                <span v-if="item.provider">{{ item.provider.commercial_name }}</span>
                                <span v-else class="text-error text-xs font-semibold">Desvinculado</span>
                            </td>
                            <td class="admin-table-td">
                                <span v-if="item.category" class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">{{ item.category.name }}</span>
                                <span v-else class="text-muted-foreground/40 italic text-xs">Sin asignar</span>
                            </td>
                            <td class="admin-table-td text-center font-mono text-xs">
                                {{ item.sort_order }}
                            </td>
                            <td class="admin-table-td text-center">
                                <span v-if="item.is_featured" class="badge-info">Destacado</span>
                                <span v-else class="text-muted-foreground/30">—</span>
                            </td>
                            <td class="admin-table-td text-center">
                                <span :class="item.is_active ? 'badge-success' : 'badge-error'">
                                    {{ item.is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="admin-table-td text-right">
                                <div class="inline-flex items-center gap-2">
                                    <Link
                                        :href="route('admin.catalog.brands.edit', { brand: item.id })"
                                        class="px-2 py-1 bg-secondary text-secondary-foreground text-xs font-semibold rounded border border-border hover:bg-neutral-200"
                                    >
                                        Configurar
                                    </Link>
                                    <button
                                        v-if="can_manage"
                                        type="button"
                                        @click="deleteBrand(item.id)"
                                        class="px-2 py-1 bg-destructive/5 text-destructive border border-destructive/20 text-xs font-semibold rounded hover:bg-destructive/10"
                                    >
                                        Neutralizar
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="brands.meta && brands.meta.last_page > 1" class="flex items-center justify-between bg-card p-4 border border-border rounded-md shadow-flat">
                <div class="text-xs text-muted-foreground font-medium">
                    Mostrando registros de marcas. Página {{ brands.meta.current_page }} de {{ brands.meta.last_page }} (Total: {{ brands.meta.total }})
                </div>
                <div class="flex items-center gap-1">
                    <template v-for="(link, index) in brands.links" :key="index">
                        <div 
                            v-if="link.url === null" 
                            class="px-2.5 py-1 text-xs text-muted-foreground/40 border border-border/50 rounded-sm cursor-not-allowed select-none"
                            v-html="link.label"
                        />
                        <Link
                            v-else
                            :href="link.url"
                            class="px-2.5 py-1 text-xs font-semibold rounded-sm border transition-colors"
                            :class="[link.active ? 'bg-primary text-primary-foreground border-primary' : 'bg-card text-foreground border-border hover:bg-secondary']"
                            v-html="link.label"
                        />
                    </template>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>