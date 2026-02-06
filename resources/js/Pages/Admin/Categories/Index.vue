<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import debounce from 'lodash/debounce';
import { 
    Search, Plus, FolderOpen, ChevronDown, 
    Folder, Layers, Eye, EyeOff, Settings, Trash2, 
    TrendingUp, AlertCircle, Hash, Globe, MoreVertical
} from 'lucide-vue-next';

const props = defineProps({
    categories: Array,
    filters: Object,
    can_manage: Boolean
});

// --- ESTADO ---
const search = ref(props.filters.search || '');
const expandedCategories = ref([]); // Almacena IDs de categorías desplegadas
const showInactive = ref(true);

// --- LÓGICA DE DATOS (Árbol Jerárquico) ---
const treeData = computed(() => {
    // Separa padres (sin parent_id) de hijos
    const parents = props.categories.filter(c => !c.parent_id);
    return parents.map(parent => ({
        ...parent,
        // Asigna hijos correspondientes
        children: props.categories.filter(c => c.parent_id === parent.id)
    }));
});

// --- FILTRADO Y BÚSQUEDA ---
const filteredTree = computed(() => {
    let result = treeData.value;
    
    // 1. Filtro por texto
    if (search.value) {
        const term = search.value.toLowerCase();
        result = result.filter(parent => {
            const matchParent = parent.name.toLowerCase().includes(term);
            const matchChild = parent.children.some(child => child.name.toLowerCase().includes(term));
            
            // Auto-expandir si un hijo coincide con la búsqueda
            if (matchChild && !expandedCategories.value.includes(parent.id)) {
                expandedCategories.value.push(parent.id);
            }
            return matchParent || matchChild;
        });
    }
    
    // 2. Filtro de inactivos
    if (!showInactive.value) {
        result = result.filter(parent => parent.is_active);
    }
    
    return result;
});

// --- KPIs PARA EL CARRUSEL ---
const statsList = computed(() => {
    const total = props.categories.length;
    const active = props.categories.filter(c => c.is_active).length;
    const subcats = props.categories.filter(c => c.parent_id).length;
    const featured = props.categories.filter(c => c.is_featured).length;
    
    return [
        { label: 'Total', value: total, icon: Layers, color: 'text-primary', bg: 'bg-primary/10' },
        { label: 'Activas', value: active, icon: Eye, color: 'text-success', bg: 'bg-success/10' },
        { label: 'Subcategorías', value: subcats, icon: FolderOpen, color: 'text-info', bg: 'bg-info/10' },
        { label: 'Destacadas', value: featured, icon: TrendingUp, color: 'text-warning', bg: 'bg-warning/10' },
    ];
});

// --- ACCIONES ---
const toggleExpand = (id) => {
    const index = expandedCategories.value.indexOf(id);
    if (index === -1) expandedCategories.value.push(id);
    else expandedCategories.value.splice(index, 1);
};

const deleteCategory = (category) => {
    if (confirm(`¿Estás seguro de eliminar "${category.name}"? Esta acción borrará también sus subcategorías.`)) {
        router.delete(route('admin.categories.destroy', category.id));
    }
};

// Búsqueda con debounce para no saturar el servidor
watch(search, debounce((val) => {
    router.get(route('admin.categories.index'), { 
        search: val, 
        show_inactive: showInactive.value 
    }, { preserveState: true, replace: true, preserveScroll: true });
}, 300));
</script>

<template>
    <AdminLayout>
        <div class="pb-24"> <div class="flex flex-col gap-4 mb-6">
                <div class="flex justify-between items-end">
                    <div>
                        <h1 class="text-2xl font-display font-black text-foreground tracking-tight">Categorías</h1>
                        <p class="text-xs text-muted-foreground">Gestión del catálogo</p>
                    </div>
                    <button @click="showInactive = !showInactive" 
                            class="btn btn-ghost btn-sm text-xs gap-2 border border-border/50 h-8">
                        <component :is="showInactive ? Eye : EyeOff" :size="14"/>
                        <span class="hidden sm:inline">{{ showInactive ? 'Ocultar Inactivas' : 'Ver Inactivas' }}</span>
                    </button>
                </div>

                <div class="relative">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" :size="18" />
                    <input v-model="search" 
                           type="text" 
                           placeholder="Buscar categorías..." 
                           class="w-full pl-10 pr-4 py-3 bg-background border border-border rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all outline-none text-sm shadow-sm">
                </div>
            </div>

            <div class="flex overflow-x-auto snap-x snap-mandatory gap-3 pb-4 mb-2 -mx-4 px-4 md:mx-0 md:px-0 no-scrollbar touch-pan-x">
                <div v-for="(stat, index) in statsList" :key="index" 
                     class="snap-start shrink-0 w-[140px] card !p-3 flex flex-col justify-between h-24 border border-border/60 shadow-sm bg-card">
                    <div class="flex justify-between items-start">
                        <span class="text-[10px] font-black uppercase tracking-wider text-muted-foreground">{{ stat.label }}</span>
                        <div :class="`p-1.5 rounded-full ${stat.bg} ${stat.color}`">
                            <component :is="stat.icon" :size="14" />
                        </div>
                    </div>
                    <span class="text-2xl font-display font-black text-foreground tracking-tight">{{ stat.value }}</span>
                </div>
            </div>

            <div class="space-y-3">
                <TransitionGroup name="list">
                    <div v-for="parent in filteredTree" :key="parent.id" 
                         class="card border-border/50 overflow-hidden transition-all duration-300 bg-card"
                         :class="expandedCategories.includes(parent.id) ? 'ring-2 ring-primary/20 shadow-md' : 'shadow-sm'">
                        
                        <div class="p-3 sm:p-4 flex items-center gap-3 cursor-pointer hover:bg-muted/30 transition-colors select-none relative"
                             @click="toggleExpand(parent.id)">
                            
                            <div class="relative shrink-0">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-muted to-muted/50 border border-border flex items-center justify-center overflow-hidden">
                                    <img v-if="parent.image_url" :src="parent.image_url" class="w-full h-full object-cover">
                                    <Folder v-else :size="20" class="text-muted-foreground/60" />
                                </div>
                                <div v-if="!parent.is_active" class="absolute -bottom-1 -right-1 bg-muted border border-background rounded-full p-0.5 shadow-sm">
                                    <EyeOff :size="10" class="text-muted-foreground"/>
                                </div>
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-start">
                                    <h3 class="font-bold text-foreground truncate text-base leading-tight pr-6">
                                        {{ parent.name }}
                                    </h3>
                                    <div class="absolute right-4 top-1/2 -translate-y-1/2">
                                        <ChevronDown :size="20" class="text-muted-foreground transition-transform duration-300" 
                                                     :class="expandedCategories.includes(parent.id) ? 'rotate-180 text-primary' : ''"/>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-xs text-muted-foreground flex items-center gap-1 font-medium bg-muted/30 px-1.5 py-0.5 rounded">
                                        {{ parent.children.length }} subcategorías
                                    </span>
                                    <span v-if="parent.external_code" class="text-[10px] text-muted-foreground font-mono">
                                        #{{ parent.external_code }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div v-show="expandedCategories.includes(parent.id)">
                            
                            <div class="flex border-t border-border/50 divide-x divide-border/50 bg-muted/10">
                                <Link v-if="can_manage" :href="route('admin.categories.edit', parent.id)" 
                                      class="flex-1 py-3 flex items-center justify-center gap-2 text-xs font-bold text-foreground hover:bg-background transition-colors">
                                    <Settings :size="14"/> Configurar
                                </Link>
                                <Link v-if="can_manage" :href="route('admin.categories.create', { parent: parent.id })"
                                      class="flex-1 py-3 flex items-center justify-center gap-2 text-xs font-bold text-primary hover:bg-primary/5 transition-colors">
                                    <Plus :size="14"/> Añadir Hija
                                </Link>
                            </div>

                            <div class="bg-muted/5 border-t border-border/50 p-2 sm:p-3 space-y-2">
                                <div v-if="parent.children.length === 0" class="text-center py-4 text-xs text-muted-foreground italic flex flex-col items-center">
                                    <span class="opacity-50">Sin subcategorías</span>
                                </div>

                                <div v-for="child in parent.children" :key="child.id" 
                                     class="flex items-center justify-between p-3 rounded-lg bg-background border border-border/40 hover:border-primary/30 transition-colors shadow-sm ml-4 relative before:absolute before:left-[-12px] before:top-1/2 before:w-3 before:h-[2px] before:bg-border/50">
                                    
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="w-2 h-2 rounded-full bg-primary/40 shrink-0"></div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-bold text-foreground truncate">{{ child.name }}</p>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                <p v-if="child.external_code" class="text-[10px] text-muted-foreground font-mono bg-muted px-1 rounded">
                                                    {{ child.external_code }}
                                                </p>
                                                <span v-if="!child.is_active" class="text-[9px] text-error font-bold uppercase">Inactiva</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-1">
                                        <Link v-if="can_manage" :href="route('admin.categories.edit', child.id)" class="btn btn-ghost btn-sm w-8 h-8 p-0 hover:bg-muted">
                                            <Settings :size="14" class="text-muted-foreground"/>
                                        </Link>
                                        <button v-if="can_manage" @click="deleteCategory(child)" class="btn btn-ghost btn-sm w-8 h-8 p-0 hover:bg-error/10 hover:text-error">
                                            <Trash2 :size="14" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </TransitionGroup>

                <div v-if="filteredTree.length === 0" class="flex flex-col items-center justify-center py-16 text-center opacity-60">
                    <div class="w-16 h-16 bg-muted/50 rounded-full flex items-center justify-center mb-4">
                        <FolderOpen :size="32" class="text-muted-foreground" />
                    </div>
                    <p class="text-sm font-medium">No se encontraron categorías</p>
                    <p class="text-xs text-muted-foreground mt-1">Prueba con otra búsqueda o crea una nueva.</p>
                </div>
            </div>

            <Link v-if="can_manage" :href="route('admin.categories.create')" 
                class="fixed bottom-24 md:bottom-8 right-4 md:right-8 z-[100] flex items-center justify-center w-14 h-14 rounded-2xl bg-primary text-primary-foreground shadow-[0_8px_30px_rgba(var(--primary),0.4)] hover:scale-110 active:scale-95 transition-all duration-300 group border border-white/20">
                <Plus :size="28" stroke-width="3" class="group-hover:rotate-90 transition-transform duration-300"/>
                <span class="sr-only">Nueva Categoría</span>
            </Link>

        </div>
    </AdminLayout>
</template>

<style scoped>
/* Utilidad para ocultar scrollbar en carrusel móvil */
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

/* Animaciones de lista */
.list-enter-active,
.list-leave-active {
    transition: all 0.3s ease;
}
.list-enter-from,
.list-leave-to {
    opacity: 0;
    transform: translateY(10px);
}
</style>