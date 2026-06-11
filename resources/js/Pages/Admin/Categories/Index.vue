<script setup>
import { ref } from 'vue';
import { router, Head, Link } from '@inertiajs/vue3';
import { 
    FolderTree, Plus, Edit2, Trash2, ArrowUpDown, 
    ChevronDown, ChevronRight, Eye, EyeOff, Star, Search
} from 'lucide-vue-next';
import CategoryDrawer from './Components/CategoryDrawer.vue';
import SkuOrderModal from './Components/SkuOrderModal.vue';

const props = defineProps({
    categories: Object,
    parents: Array,
    filters: Object,
    can_manage: Boolean
});

const search = ref(props.filters.search || '');
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
</script>

<template>
    <Head title="Góndola - Categorías" />

    <div class="p-6 max-w-7xl mx-auto space-y-6">
        <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4 bg-card p-4 rounded-xl border border-border shadow-sm">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-primary/10 rounded-lg text-primary">
                    <FolderTree :size="24" />
                </div>
                <div>
                    <h1 class="font-sans font-bold text-xl text-foreground">Pasillos y Categorías</h1>
                    <p class="text-xs text-muted-foreground">Estructura jerárquica del catálogo OLTP</p>
                </div>
            </div>
            
            <button v-if="can_manage" @click="openCreateDrawer" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-primary text-primary-foreground text-sm font-medium rounded-lg shadow-sm hover:bg-primary/90 transition-colors">
                <Plus :size="16" />
                Materializar Categoría
            </button>
        </div>

        <div class="flex items-center gap-2 max-w-md bg-card border border-border rounded-lg px-3 py-2 focus-within:ring-1 focus-within:ring-primary">
            <Search :size="18" class="text-muted-foreground" />
            <input v-model="search" @input="handleSearch" type="text" placeholder="Buscar por denominación o código externo..." class="w-full bg-transparent text-sm text-foreground outline-none border-none p-0 focus:ring-0" />
        </div>

        <div class="bg-card rounded-xl border border-border shadow-sm overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-muted/40 text-muted-foreground text-xs font-semibold uppercase border-b border-border">
                        <th class="p-4 w-10"></th>
                        <th class="p-4">Estructura / Nombre</th>
                        <th class="p-4">Código Ext.</th>
                        <th class="p-4 w-24 text-center">Estado</th>
                        <th class="p-4 w-24 text-center">Destacado</th>
                        <th class="p-4 w-20 text-center">Orden</th>
                        <th class="p-4 w-44 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-border">
                    <template v-for="category in categories.data" :key="category.id">
                        <tr class="hover:bg-muted/20 transition-colors">
                            <td class="p-4">
                                <button v-if="category.children && category.children.length" @click="toggleRow(category.id)" class="p-1 rounded hover:bg-muted text-muted-foreground">
                                    <ChevronDown v-if="expandedRows.has(category.id)" :size="16" />
                                    <ChevronRight v-else :size="16" />
                                </button>
                            </td>
                            <td class="p-4 font-medium text-foreground">
                                <div class="flex items-center gap-3">
                                    <span class="w-3 h-3 rounded-full border border-border" :style="{ backgroundColor: category.bg_color }"></span>
                                    {{ category.name }}
                                </div>
                            </td>
                            <td class="p-4 text-muted-foreground font-mono text-xs">{{ category.external_code || 'N/A' }}</td>
                            <td class="p-4 text-center">
                                <span :class="category.is_active ? 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20' : 'bg-destructive/10 text-destructive border-destructive/20'" class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full border">
                                    <Eye v-if="category.is_active" :size="12" class="mr-1" />
                                    <EyeOff v-else :size="12" class="mr-1" />
                                    {{ category.is_active ? 'Activo' : 'Oculto' }}
                                </span>
                            </td>
                            <td class="p-4 text-center">
                                <Star :size="16" :class="category.is_featured ? 'text-amber-500 fill-amber-500' : 'text-muted-foreground/40'" class="mx-auto" />
                            </td>
                            <td class="p-4 text-center font-mono text-xs">{{ category.sort_order }}</td>
                            <td class="p-4 text-right space-x-1 whitespace-nowrap">
                                <button @click="openSkuModal(category)" class="inline-flex items-center p-1.5 text-muted-foreground hover:text-primary hover:bg-primary/10 rounded-md transition-colors" title="Ordenar Góndola">
                                    <ArrowUpDown :size="16" />
                                </button>
                                <button @click="openEditDrawer(category)" class="inline-flex items-center p-1.5 text-muted-foreground hover:text-foreground hover:bg-muted rounded-md transition-colors" title="Editar Atributos">
                                    <Edit2 :size="16" />
                                </button>
                                <button @click="deleteCategory(category)" class="inline-flex items-center p-1.5 text-destructive hover:bg-destructive/10 rounded-md transition-colors" title="Neutralizar">
                                    <Trash2 :size="16" />
                                </button>
                            </td>
                        </tr>

                        <template v-if="expandedRows.has(category.id) && category.children">
                            <tr v-for="child in category.children" :key="child.id" class="bg-muted/10 hover:bg-muted/20 transition-colors">
                                <td class="p-4"></td>
                                <td class="p-4 pl-10 text-muted-foreground">
                                    <div class="flex items-center gap-2">
                                        <span class="text-muted-foreground/40">└──</span>
                                        {{ child.name }}
                                    </div>
                                </td>
                                <td class="p-4 text-muted-foreground font-mono text-xs">{{ child.external_code || 'N/A' }}</td>
                                <td class="p-4 text-center">
                                    <span :class="child.is_active ? 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20' : 'bg-destructive/10 text-destructive border-destructive/20'" class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full border">
                                        {{ child.is_active ? 'Activo' : 'Oculto' }}
                                    </span>
                                </td>
                                <td class="p-4 text-center">
                                    <Star :size="14" :class="child.is_featured ? 'text-amber-500 fill-amber-500' : 'text-muted-foreground/30'" class="mx-auto" />
                                </td>
                                <td class="p-4 text-center font-mono text-xs">{{ child.sort_order }}</td>
                                <td class="p-4 text-right space-x-1 whitespace-nowrap">
                                    <button @click="openSkuModal(child)" class="inline-flex items-center p-1.5 text-muted-foreground hover:text-primary hover:bg-primary/10 rounded-md transition-colors">
                                        <ArrowUpDown :size="14" />
                                    </button>
                                    <button @click="openEditDrawer(child)" class="inline-flex items-center p-1.5 text-muted-foreground hover:text-foreground hover:bg-muted rounded-md transition-colors">
                                        <Edit2 :size="14" />
                                    </button>
                                    <button @click="deleteCategory(child)" class="inline-flex items-center p-1.5 text-destructive hover:bg-destructive/10 rounded-md transition-colors">
                                        <Trash2 :size="14" />
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </template>
                    <tr v-if="categories.data.length === 0">
                        <td colspan="7" class="p-8 text-center text-muted-foreground">
                            Ninguna categoría intersecta los parámetros de búsqueda actuales.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <CategoryDrawer :show="isDrawerOpen" :category="selectedCategory" :parents="parents" @close="isDrawerOpen = false" />
    <SkuOrderModal :show="isModalOpen" :category="selectedCategory" @close="isModalOpen = false" />
</template>