<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import debounce from 'lodash/debounce';
import { 
    Search, Plus, FolderOpen, ChevronDown, 
    Folder, Layers, Eye, EyeOff, Settings, Trash2, 
    TrendingUp, Star, ShieldAlert, ReceiptText, Info
} from 'lucide-vue-next';

// Props recibe datos ya procesados por CategoryResource (incluyendo image_url)
const props = defineProps({
    categories: Array, 
    filters: Object,
    can_manage: Boolean
});

// --- ESTADO ---
const search = ref(props.filters.search || '');
const showInactive = ref(props.filters.show_inactive !== 'false');
const expandedCategories = ref([]);

// --- TRANSFORMACIÓN JERÁRQUICA (O(n) Performance) ---
// Convierte la lista plana del servidor en un árbol visual
const treeData = computed(() => {
    const nodes = JSON.parse(JSON.stringify(props.categories)); // Deep copy para evitar mutaciones
    const map = {};
    const tree = [];

    // Mapear por ID e inicializar hijos
    nodes.forEach(node => { map[node.id] = { ...node, children: [] }; });

    // Construir árbol
    nodes.forEach(node => {
        if (node.parent_id && map[node.parent_id]) {
            map[node.parent_id].children.push(map[node.id]);
        } else if (!node.parent_id) {
            tree.push(map[node.id]);
        }
    });

    return tree;
});

// --- KPIs ---
const statsList = computed(() => [
    { label: 'Total', value: props.categories.length, icon: Layers, color: 'text-primary', bg: 'bg-primary/10' },
    { label: 'Activas', value: props.categories.filter(c => c.is_active).length, icon: Eye, color: 'text-success', bg: 'bg-success/10' },
    { label: 'Restricción +18', value: props.categories.filter(c => c.requires_age_check).length, icon: ShieldAlert, color: 'text-error', bg: 'bg-error/10' },
]);

// --- ACCIONES ---
const toggleExpand = (id) => {
    const index = expandedCategories.value.indexOf(id);
    if (index === -1) expandedCategories.value.push(id);
    else expandedCategories.value.splice(index, 1);
};

const deleteCategory = (category) => {
    if (confirm(`¿Eliminar "${category.name}"? Se validará que no tenga dependencias.`)) {
        router.delete(route('admin.categories.destroy', category.id), { preserveScroll: true });
    }
};

// Sincronización con Servidor (Búsqueda y Filtros)
const updateFilters = debounce(() => {
    router.get(route('admin.categories.index'), { 
        search: search.value, 
        show_inactive: showInactive.value 
    }, { preserveState: true, replace: true, preserveScroll: true });
}, 300);

watch([search, showInactive], updateFilters);
</script>

<template>
    <AdminLayout>
        <div class="max-w-6xl mx-auto pb-24 px-4 sm:px-6">
            
            <div class="flex flex-col md:flex-row justify-between items-end gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-black tracking-tighter">Catálogo Maestro</h1>
                    <p class="text-sm text-muted-foreground font-medium">Gestión jerárquica de categorías</p>
                </div>
                
                <div class="flex items-center gap-3 w-full md:w-auto">
                     <div class="relative group flex-1 md:w-64">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" :size="18" />
                        <input v-model="search" type="text" placeholder="Buscar..." 
                               class="w-full pl-10 pr-4 py-2.5 bg-background border border-border rounded-xl text-sm focus:ring-2 focus:ring-primary/20 transition-all">
                    </div>
                    <button @click="showInactive = !showInactive" 
                            class="btn btn-sm border border-border/40 gap-2 h-[42px] px-3 rounded-xl transition-all"
                            :class="showInactive ? 'bg-primary/10 text-primary font-bold' : 'bg-background text-muted-foreground'">
                        <component :is="showInactive ? Eye : EyeOff" :size="18"/>
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4 mb-6">
                <div v-for="(stat, index) in statsList" :key="index" class="card !p-4 flex items-center gap-4 border-border/50">
                    <div :class="`p-3 rounded-xl ${stat.bg} ${stat.color}`">
                        <component :is="stat.icon" :size="20" />
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase text-muted-foreground">{{ stat.label }}</p>
                        <p class="text-2xl font-black">{{ stat.value }}</p>
                    </div>
                </div>
            </div>

            <div class="space-y-3">
                <TransitionGroup name="list">
                    <div v-for="parent in treeData" :key="parent.id" 
                         class="card overflow-hidden transition-all hover:border-primary/30 group"
                         :class="{'ring-2 ring-primary/10': expandedCategories.includes(parent.id)}">
                        
                        <div class="p-4 flex items-center gap-4 cursor-pointer select-none relative"
                             @click="toggleExpand(parent.id)">
                            
                            <div class="w-16 h-16 rounded-lg bg-muted/50 border border-border/50 overflow-hidden shrink-0 relative">
                                <img v-if="parent.image_url" :src="parent.image_url" class="w-full h-full object-cover" alt="Category Icon">
                                <div v-else class="w-full h-full flex items-center justify-center bg-muted">
                                    <Folder :size="24" class="text-muted-foreground/30" />
                                </div>
                                <div v-if="!parent.is_active" class="absolute inset-0 bg-background/60 backdrop-blur-[1px] flex items-center justify-center">
                                    <EyeOff :size="16" class="text-muted-foreground"/>
                                </div>
                            </div>

                            <div class="flex-1 min-w-0 flex flex-col justify-center gap-1">
                                <div class="flex items-center gap-2">
                                    <h3 class="font-bold text-lg truncate leading-tight">{{ parent.name }}</h3>
                                    <Star v-if="parent.is_featured" :size="16" class="text-warning fill-warning animate-pulse" />
                                    
                                    <div v-if="parent.description" class="has-tooltip cursor-help">
                                        <Info :size="14" class="text-muted-foreground"/>
                                        <div class="tooltip pointer-events-none absolute bg-popover text-popover-foreground text-xs p-2 rounded shadow-lg -mt-8 -ml-2 w-48 z-50 border">
                                            {{ parent.description }}
                                        </div>
                                    </div>
                                </div>

                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="badge badge-sm bg-primary/10 text-primary border-0 font-bold">
                                        {{ parent.children.length }} Subcategorías
                                    </span>
                                    <span v-if="parent.external_code" class="badge badge-sm badge-outline font-mono text-[10px]">
                                        #{{ parent.external_code }}
                                    </span>
                                    <span v-if="parent.tax_classification" class="badge badge-sm bg-info/10 text-info border-0 gap-1 pl-1">
                                        <ReceiptText :size="12"/> {{ parent.tax_classification }}
                                    </span>
                                    <span v-if="parent.requires_age_check" class="badge badge-sm bg-error/10 text-error border-0 gap-1 pl-1 font-bold">
                                        <ShieldAlert :size="12"/> +18
                                    </span>
                                </div>
                            </div>

                             <ChevronDown :size="24" class="text-muted-foreground transition-transform duration-300 shrink-0" 
                                         :class="expandedCategories.includes(parent.id) ? 'rotate-180 text-primary' : ''"/>
                        </div>

                        <div v-show="expandedCategories.includes(parent.id)">
                            <div v-if="can_manage" class="flex border-t border-border/50 divide-x divide-border/50 bg-muted/30 text-xs font-bold uppercase tracking-wider">
                                <Link :href="route('admin.categories.create', { parent: parent.id })" class="flex-1 py-2.5 flex justify-center items-center gap-2 hover:bg-primary/5 hover:text-primary transition-colors">
                                    <Plus :size="14"/> Añadir Hija
                                </Link>
                                <Link :href="route('admin.categories.edit', parent.id)" class="flex-1 py-2.5 flex justify-center items-center gap-2 hover:bg-background transition-colors">
                                    <Settings :size="14"/> Editar
                                </Link>
                                <button @click.stop="deleteCategory(parent)" class="flex-1 py-2.5 flex justify-center items-center gap-2 text-error hover:bg-error/5 transition-colors">
                                    <Trash2 :size="14"/> Eliminar
                                </button>
                            </div>

                            <div class="p-4 bg-muted/10 space-y-2">
                                <div v-for="child in parent.children" :key="child.id" 
                                     class="flex items-center justify-between p-2 pr-3 rounded-lg bg-background border border-border/60 hover:border-primary/30 transition-all ml-8 relative group/child">
                                    <div class="absolute -left-8 top-1/2 w-8 h-[2px] bg-border/60 rounded-l-full"></div>
                                    
                                    <div class="flex items-center gap-3 min-w-0 flex-1">
                                        <div class="w-10 h-10 rounded-md bg-muted border border-border/40 overflow-hidden shrink-0 relative">
                                             <img v-if="child.image_url" :src="child.image_url" class="w-full h-full object-cover">
                                             <div v-else class="w-full h-full flex items-center justify-center">
                                                <FolderOpen :size="16" class="text-muted-foreground/30" />
                                            </div>
                                             <div v-if="!child.is_active" class="absolute inset-0 bg-background/60"></div>
                                        </div>

                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center gap-2">
                                                <p class="font-bold truncate text-sm" :class="{'opacity-50': !child.is_active}">
                                                    {{ child.name }}
                                                </p>
                                                <span v-if="child.requires_age_check" class="text-[10px] font-black text-error flex items-center"><ShieldAlert :size="10"/>+18</span>
                                                <span v-if="child.tax_classification" class="text-[10px] text-info"><ReceiptText :size="10"/></span>
                                            </div>
                                             <p v-if="child.external_code" class="text-[10px] font-mono text-muted-foreground">{{ child.external_code }}</p>
                                        </div>
                                    </div>

                                    <div v-if="can_manage" class="flex items-center gap-1 opacity-0 group-hover/child:opacity-100 transition-opacity">
                                        <Link :href="route('admin.categories.edit', child.id)" class="btn btn-ghost btn-xs h-8 w-8 p-0 hover:bg-muted">
                                            <Settings :size="14" class="text-muted-foreground"/>
                                        </Link>
                                        <button @click="deleteCategory(child)" class="btn btn-ghost btn-xs h-8 w-8 p-0 hover:bg-error/10 text-error">
                                            <Trash2 :size="14" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </TransitionGroup>

                <div v-if="treeData.length === 0 && !search" class="py-12 text-center border-2 border-dashed border-border/50 rounded-2xl">
                    <Layers :size="40" class="mx-auto text-muted-foreground/30 mb-3" />
                    <h3 class="font-bold text-lg">Catálogo Vacío</h3>
                    <p class="text-sm text-muted-foreground mb-4">Comienza definiendo la estructura principal.</p>
                    <Link v-if="can_manage" :href="route('admin.categories.create')" class="btn btn-primary btn-sm">
                        Crear Primera Categoría
                    </Link>
                </div>
                 <div v-else-if="treeData.length === 0 && search" class="py-8 text-center text-muted-foreground">
                    No se encontraron resultados para "{{ search }}".
                </div>
            </div>

            <Link v-if="can_manage" :href="route('admin.categories.create')" 
                  class="fixed bottom-8 right-8 w-14 h-14 rounded-full bg-primary text-primary-foreground shadow-lg shadow-primary/30 flex items-center justify-center hover:scale-105 active:scale-95 transition-all z-50">
                <Plus :size="24" stroke-width="3" />
            </Link>
        </div>
    </AdminLayout>
</template>

<style scoped>
/* Tooltip simple CSS */
.has-tooltip:hover .tooltip {
    display: block;
    animation: fade-in 0.2s ease-out;
}
.tooltip { display: none; }

@keyframes fade-in {
    from { opacity: 0; transform: translateY(5px); }
    to { opacity: 1; transform: translateY(0); }
}

.list-move,
.list-enter-active,
.list-leave-active { transition: all 0.3s ease; }
.list-enter-from,
.list-leave-to { opacity: 0; transform: scale(0.98); }
.list-leave-active { position: absolute; width: 100%; }
</style>