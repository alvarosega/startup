<script setup>
import { ref } from 'vue';
import { router, Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import CategoryDrawer from './Components/CategoryDrawer.vue';
import SkuOrderModal from './Components/SkuOrderModal.vue';

const props = defineProps({
    categories: Object,
    parents: Array,
    filters: Object,
    can_manage: Boolean
});

const search = ref(props.filters?.search || '');
const expandedRows = ref(new Set());
const isDrawerOpen = ref(false);
const isModalOpen = ref(false);
const selectedCategory = ref(null);

const handleSearch = () => {
    router.get(route('admin.categories.index'), { search: search.value }, {
        preserveState: true,
        replace: true
    });
};

const toggleRow = (id) => {
    if (expandedRows.value.has(id)) {
        expandedRows.value.delete(id);
    } else {
        expandedRows.value.add(id);
    }
};

const openCreateDrawer = () => {
    selectedCategory.value = null;
    isDrawerOpen.value = true;
};

const openEditDrawer = (category) => {
    selectedCategory.value = category;
    isDrawerOpen.value = true;
};

const openSkuModal = (category) => {
    selectedCategory.value = category;
    isModalOpen.value = true;
};

const deleteCategory = (category) => {
    if (confirm(`¿Confirmar neutralización estricta de la categoría: ${category.name}?`)) {
        router.delete(route('admin.categories.destroy', category.id));
    }
};

const clearFilters = () => {
    search.value = '';
    handleSearch();
};
</script>

<template>
    <Head title="Góndola - Categorías" />
    <AdminLayout>
        
        <template #header>
            <div class="flex items-center justify-between w-full select-none">
                <div>
                    <h1 class="text-xl md:text-2xl font-black tracking-tight text-foreground uppercase italic">Pasillos y Categorías</h1>
                    <p class="text-[10px] text-muted-foreground font-mono tracking-wider mt-0.5 uppercase">Estructura jerárquica del catálogo OLTP</p>
                </div>
                <button v-if="can_manage" @click="openCreateDrawer" 
                        class="admin-btn-primary inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wider">
                    <span class="material-symbols-rounded text-sm">layers</span>
                    <span>MATERIALIZAR_CATEGORÍA</span>
                </button>
            </div>
        </template>

        <div class="space-y-4">
            
            <div class="flex items-center gap-2 max-w-md bg-card border border-border rounded-md px-3 py-1.5 shadow-flat select-none">
                <span class="material-symbols-rounded text-muted-foreground text-lg">search</span>
                <input v-model="search" @input="handleSearch" type="text" 
                       placeholder="Buscar por denominación o código externo..." 
                       class="w-full bg-transparent text-xs text-foreground outline-none border-none p-0 focus:ring-0 font-mono uppercase placeholder:text-muted-foreground/50" />
            </div>

            <div class="w-full overflow-x-auto border border-border rounded-md bg-card shadow-flat">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th class="admin-table-th w-12 text-center select-none">DESPLIEGUE</th>
                            <th class="admin-table-th min-w-[280px]">ESTRUCTURA / DENOMINACIÓN DEL NODO</th>
                            <th class="admin-table-th w-40">CÓDIGO EXT.</th>
                            <th class="admin-table-th w-28 text-center select-none">ESTADO</th>
                            <th class="admin-table-th w-28 text-center select-none">DESTACADO</th>
                            <th class="admin-table-th w-20 text-center select-none">ORDEN</th>
                            <th class="admin-table-th w-36 text-center select-none">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-for="category in categories.data" :key="category.id">
                            
                            <tr class="admin-table-tr bg-card">
                                <td class="admin-table-td text-center select-none">
                                    <button v-if="category.children && category.children.length" 
                                            @click="toggleRow(category.id)" 
                                            class="p-1 rounded-md hover:bg-neutral-100 dark:hover:bg-neutral-800 text-muted-foreground transition-colors flex items-center justify-center mx-auto border border-border/40">
                                        <span class="material-symbols-rounded text-base">
                                            {{ expandedRows.has(category.id) ? 'keyboard_arrow_down' : 'keyboard_arrow_right' }}
                                        </span>
                                    </button>
                                </td>
                                <td class="admin-table-td font-semibold text-foreground">
                                    <div class="flex items-center gap-2">
                                        <span class="w-3 h-3 rounded-sm border border-black/10 shrink-0 select-none block" 
                                              :style="{ backgroundColor: category.bg_color || 'var(--border)' }"></span>
                                        <span class="uppercase tracking-tight text-xs">{{ category.name }}</span>
                                    </div>
                                </td>
                                <td class="admin-table-td text-muted-foreground font-mono text-xs select-all">
                                    {{ category.external_code || 'N/A' }}
                                </td>
                                <td class="admin-table-td text-center select-none">
                                    <span :class="category.is_active ? 'badge-success' : 'badge-error'">
                                        {{ category.is_active ? 'ACTIVO' : 'OCULTO' }}
                                    </span>
                                </td>
                                <td class="admin-table-td text-center select-none">
                                    <span class="material-symbols-rounded text-base align-middle"
                                          :class="category.is_featured ? 'text-warning' : 'text-muted-foreground/30'"
                                          :style="{ fontVariationSettings: category.is_featured ? `'FILL' 1` : `'FILL' 0` }">
                                        star
                                    </span>
                                </td>
                                <td class="admin-table-td text-center font-mono text-xs text-muted-foreground select-none">
                                    {{ String(category.sort_order).padStart(2, '0') }}
                                </td>
                                <td class="admin-table-td text-center select-none">
                                    <div class="flex items-center justify-center gap-1">
                                        <button @click="openSkuModal(category)" 
                                                class="p-1.5 text-muted-foreground hover:text-primary rounded-md hover:bg-primary/10 transition-colors flex items-center justify-center" 
                                                title="Ordenar Góndola">
                                            <span class="material-symbols-rounded text-base">swap_vert</span>
                                        </button>
                                        <button @click="openEditDrawer(category)" 
                                                class="p-1.5 text-muted-foreground hover:text-foreground rounded-md hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors flex items-center justify-center" 
                                                title="Editar Atributos">
                                            <span class="material-symbols-rounded text-base">edit</span>
                                        </button>
                                        <button @click="deleteCategory(category)" 
                                                class="p-1.5 text-neutral-300 hover:text-destructive rounded-md hover:bg-destructive/10 transition-colors flex items-center justify-center" 
                                                title="Neutralizar">
                                            <span class="material-symbols-rounded text-base">delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <template v-if="expandedRows.has(category.id) && category.children">
                                <tr v-for="child in category.children" :key="child.id" 
                                    class="admin-table-tr bg-neutral-50/50 dark:bg-neutral-900/20">
                                    <td class="admin-table-td"></td>
                                    <td class="admin-table-td text-muted-foreground font-medium pl-6">
                                        <div class="flex items-center gap-2 select-none">
                                            <span class="text-border font-mono text-xs">└──</span>
                                            <span class="text-foreground uppercase tracking-tight text-xs">{{ child.name }}</span>
                                        </div>
                                    </td>
                                    <td class="admin-table-td text-muted-foreground font-mono text-xs select-all">
                                        {{ child.external_code || 'N/A' }}
                                    </td>
                                    <td class="admin-table-td text-center select-none">
                                        <span :class="child.is_active ? 'badge-success' : 'badge-error'">
                                            {{ child.is_active ? 'ACTIVO' : 'OCULTO' }}
                                        </span>
                                    </td>
                                    <td class="admin-table-td text-center select-none">
                                        <span class="material-symbols-rounded text-sm align-middle"
                                              :class="child.is_featured ? 'text-warning' : 'text-muted-foreground/30'"
                                              :style="{ fontVariationSettings: child.is_featured ? `'FILL' 1` : `'FILL' 0` }">
                                            star
                                        </span>
                                    </td>
                                    <td class="admin-table-td text-center font-mono text-xs text-muted-foreground select-none">
                                        {{ String(child.sort_order).padStart(2, '0') }}
                                    </td>
                                    <td class="admin-table-td text-center select-none">
                                        <div class="flex items-center justify-center gap-1">
                                            <button @click="openSkuModal(child)" 
                                                    class="p-1.5 text-muted-foreground hover:text-primary rounded-md hover:bg-primary/10 transition-colors flex items-center justify-center">
                                                <span class="material-symbols-rounded text-base">swap_vert</span>
                                            </button>
                                            <button @click="openEditDrawer(child)" 
                                                    class="p-1.5 text-muted-foreground hover:text-foreground rounded-md hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors flex items-center justify-center">
                                                <span class="material-symbols-rounded text-base">edit</span>
                                            </button>
                                            <button @click="deleteCategory(child)" 
                                                    class="p-1.5 text-neutral-300 hover:text-destructive rounded-md hover:bg-destructive/10 transition-colors flex items-center justify-center">
                                                <span class="material-symbols-rounded text-base">delete</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </template>

                        <tr v-if="categories.data.length === 0">
                            <td colspan="7" class="p-16 text-center select-none bg-card border-b border-border">
                                <span class="material-symbols-rounded text-4xl text-neutral-300 block mb-2">search_off</span>
                                <h3 class="text-muted-foreground font-mono text-xs font-bold uppercase tracking-wider">Ninguna categoría intersecta los parámetros</h3>
                                <button @click="clearFilters" 
                                        class="mt-3 text-xs font-black text-primary hover:opacity-80 uppercase tracking-wider font-mono">
                                    Reiniciar Protocolo
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <CategoryDrawer :show="isDrawerOpen" :category="selectedCategory" :parents="parents" @close="isDrawerOpen = false" />
        <SkuOrderModal :show="isModalOpen" :category="selectedCategory" @close="isModalOpen = false" />
    </AdminLayout>
</template>