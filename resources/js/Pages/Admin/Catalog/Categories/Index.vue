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
    router.get(route('admin.catalog.categories.index'), { search: search.value }, {
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
        router.delete(route('admin.catalog.categories.destroy', category.id));
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
                    <h1 class="text-xl md:text-2xl font-black tracking-tight text-neutral-900 dark:text-neutral-50 uppercase italic">Pasillos y Categorías</h1>
                    <p class="text-[10px] text-neutral-500 dark:text-neutral-400 font-mono tracking-wider mt-0.5 uppercase">Estructura jerárquica del catálogo OLTP</p>
                </div>
                <button v-if="can_manage" @click="openCreateDrawer" 
                        class="bg-neutral-900 hover:bg-neutral-800 dark:bg-neutral-50 dark:hover:bg-neutral-200 text-white dark:text-neutral-950 px-4 py-2 border border-transparent rounded-md transition-colors text-xs font-bold uppercase tracking-wider">
                    MATERIALIZAR_CATEGORÍA
                </button>
            </div>
        </template>

        <div class="space-y-4">
            
            <div class="flex items-center gap-2 max-w-md bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-md px-3 py-1.5 shadow-sm select-none">
                <span class="material-symbols-rounded text-neutral-400 text-lg">search</span>
                <input v-model="search" @input="handleSearch" type="text" 
                       placeholder="Buscar por denominación o código externo..." 
                       class="w-full bg-transparent text-xs text-neutral-900 dark:text-neutral-50 outline-none border-none p-0 focus:ring-0 font-mono uppercase placeholder:text-neutral-400/50" />
            </div>

            <div class="w-full overflow-x-auto border border-neutral-200 dark:border-neutral-800 rounded-md bg-white dark:bg-neutral-900 shadow-sm">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-neutral-200 dark:border-neutral-800 bg-neutral-50/70 dark:bg-neutral-900/50 select-none">
                            <th class="p-3 text-[10px] font-mono font-bold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 w-12 text-center">DESPLIEGUE</th>
                            <th class="p-3 text-[10px] font-mono font-bold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 min-w-[280px]">ESTRUCTURA / DENOMINACIÓN DEL NODO</th>
                            <th class="p-3 text-[10px] font-mono font-bold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 w-40">CÓDIGO EXT.</th>
                            <th class="p-3 text-[10px] font-mono font-bold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 w-28 text-center">ESTADO</th>
                            <th class="p-3 text-[10px] font-mono font-bold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 w-28 text-center">DESTACADO</th>
                            <th class="p-3 text-[10px] font-mono font-bold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 w-20 text-center">ORDEN</th>
                            <th class="p-3 text-[10px] font-mono font-bold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 w-36 text-center">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-for="category in categories.data" :key="category.id">
                            
                            <tr class="border-b border-neutral-200 dark:border-neutral-800 hover:bg-neutral-50/50 dark:hover:bg-neutral-800/30 transition-colors">
                                <td class="p-3 text-center select-none">
                                    <button v-if="category.children && category.children.length" 
                                            @click="toggleRow(category.id)" 
                                            class="p-1 rounded-md hover:bg-neutral-100 dark:hover:bg-neutral-800 text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300 transition-colors flex items-center justify-center mx-auto border border-neutral-200 dark:border-neutral-700/50">
                                        <span class="material-symbols-rounded text-base">
                                            {{ expandedRows.has(category.id) ? 'keyboard_arrow_down' : 'keyboard_arrow_right' }}
                                        </span>
                                    </button>
                                </td>
                                <td class="p-3 font-semibold text-neutral-900 dark:text-neutral-100">
                                    <div class="flex items-center gap-2">
                                        <span class="w-3 h-3 rounded-sm border border-black/10 shrink-0 select-none block" 
                                              :style="{ backgroundColor: category.bg_color || '#6366F1' }"></span>
                                        <span class="uppercase tracking-tight text-xs">{{ category.name }}</span>
                                    </div>
                                </td>
                                <td class="p-3 text-neutral-500 dark:text-neutral-400 font-mono text-xs select-all">
                                    {{ category.external_code || 'N/A' }}
                                </td>
                                <td class="p-3 text-center select-none">
                                    <span :class="category.is_active 
                                        ? 'inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800' 
                                        : 'inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-rose-50 dark:bg-rose-950/30 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-800'">
                                        {{ category.is_active ? 'ACTIVO' : 'OCULTO' }}
                                    </span>
                                </td>
                                <td class="p-3 text-center select-none">
                                    <span class="material-symbols-rounded text-base align-middle"
                                          :class="category.is_featured ? 'text-amber-500' : 'text-neutral-300 dark:text-neutral-700'"
                                          :style="{ fontVariationSettings: category.is_featured ? `'FILL' 1` : `'FILL' 0` }">
                                        star
                                    </span>
                                </td>
                                <td class="p-3 text-center font-mono text-xs text-neutral-400 select-none">
                                    {{ String(category.sort_order).padStart(2, '0') }}
                                </td>
                                <td class="p-3 text-center select-none">
                                    <div class="flex items-center justify-center gap-1">
                                        <button @click="openSkuModal(category)" 
                                                class="p-1.5 text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-50 rounded-md hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors flex items-center justify-center" 
                                                title="Ordenar Góndola">
                                            <span class="material-symbols-rounded text-base">swap_vert</span>
                                        </button>
                                        <button @click="openEditDrawer(category)" 
                                                class="p-1.5 text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-50 rounded-md hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors flex items-center justify-center" 
                                                title="Editar Atributos">
                                            <span class="material-symbols-rounded text-base">edit</span>
                                        </button>
                                        <button @click="deleteCategory(category)" 
                                                class="p-1.5 text-neutral-300 hover:text-rose-600 dark:hover:text-rose-400 rounded-md hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-colors flex items-center justify-center" 
                                                title="Neutralizar">
                                            <span class="material-symbols-rounded text-base">delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <template v-if="expandedRows.has(category.id) && category.children">
                                <tr v-for="child in category.children" :key="child.id" 
                                    class="border-b border-neutral-200 dark:border-neutral-800 bg-neutral-50/40 dark:bg-neutral-900/20 hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                                    <td class="p-3"></td>
                                    <td class="p-3 text-neutral-500 dark:text-neutral-400 font-medium pl-6">
                                        <div class="flex items-center gap-2 select-none">
                                            <span class="text-neutral-300 dark:text-neutral-700 font-mono text-xs">└──</span>
                                            <span class="text-neutral-800 dark:text-neutral-200 uppercase tracking-tight text-xs">{{ child.name }}</span>
                                        </div>
                                    </td>
                                    <td class="p-3 text-neutral-500 dark:text-neutral-400 font-mono text-xs select-all">
                                        {{ child.external_code || 'N/A' }}
                                    </td>
                                    <td class="p-3 text-center select-none">
                                        <span :class="child.is_active 
                                            ? 'inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800' 
                                            : 'inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-rose-50 dark:bg-rose-950/30 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-800'">
                                            {{ child.is_active ? 'ACTIVO' : 'OCULTO' }}
                                        </span>
                                    </td>
                                    <td class="p-3 text-center select-none">
                                        <span class="material-symbols-rounded text-sm align-middle"
                                              :class="child.is_featured ? 'text-amber-500' : 'text-neutral-300 dark:text-neutral-700'"
                                              :style="{ fontVariationSettings: child.is_featured ? `'FILL' 1` : `'FILL' 0` }">
                                            star
                                        </span>
                                    </td>
                                    <td class="p-3 text-center font-mono text-xs text-neutral-400 select-none">
                                        {{ String(child.sort_order).padStart(2, '0') }}
                                    </td>
                                    <td class="p-3 text-center select-none">
                                        <div class="flex items-center justify-center gap-1">
                                            <button @click="openSkuModal(child)" 
                                                    class="p-1.5 text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-50 rounded-md hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors flex items-center justify-center">
                                                <span class="material-symbols-rounded text-base">swap_vert</span>
                                            </button>
                                            <button @click="openEditDrawer(child)" 
                                                    class="p-1.5 text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-50 rounded-md hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors flex items-center justify-center">
                                                <span class="material-symbols-rounded text-base">edit</span>
                                            </button>
                                            <button @click="deleteCategory(child)" 
                                                    class="p-1.5 text-neutral-300 hover:text-rose-600 dark:hover:text-rose-400 rounded-md hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-colors flex items-center justify-center">
                                                <span class="material-symbols-rounded text-base">delete</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </template>

                        <tr v-if="categories.data.length === 0">
                            <td colspan="7" class="p-16 text-center select-none bg-white dark:bg-neutral-900 border-b border-neutral-200 dark:border-neutral-800">
                                <span class="material-symbols-rounded text-4xl text-neutral-300 dark:text-neutral-700 block mb-2">search_off</span>
                                <h3 class="text-neutral-400 dark:text-neutral-500 font-mono text-xs font-bold uppercase tracking-wider">Ninguna categoría intersecta los parámetros</h3>
                                <button @click="clearFilters" 
                                        class="mt-3 text-xs font-black text-neutral-900 dark:text-white hover:opacity-80 uppercase tracking-wider font-mono underline underline-offset-4">
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