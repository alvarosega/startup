<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import debounce from 'lodash/debounce';
import { 
    Search, Plus, FolderOpen, ChevronDown, 
    Folder, Layers, Eye, EyeOff, Settings, Trash2, 
    TrendingUp, Star, ShieldAlert, ReceiptText, Info,
    Cpu, Terminal, Radar, GitBranch, Wifi, WifiOff,
    Zap, Target, AlertTriangle, CheckCircle, Hash
} from 'lucide-vue-next';

const props = defineProps({
    categories: [Array, Object], // Flexibilidad para Resource o Array plano
    filters: Object,
    can_manage: Boolean
});

// --- ESTADO ---
const search = ref(props.filters.search || '');
const showInactive = ref(props.filters.show_inactive !== 'false');
const expandedCategories = ref([]);
const hoveredCategory = ref(null);

// --- NORMALIZACIÓN DE DATOS ---
const categoriesList = computed(() => {
    return Array.isArray(props.categories) ? props.categories : (props.categories.data || []);
});

// --- TRANSFORMACIÓN JERÁRQUICA OPTIMIZADA ---
const treeData = computed(() => {
    const list = categoriesList.value;
    const map = {};
    const tree = [];

    list.forEach(node => {
        map[node.id] = { ...node, children: [] };
    });

    list.forEach(node => {
        if (node.parent_id && map[node.parent_id]) {
            map[node.parent_id].children.push(map[node.id]);
        } else if (!node.parent_id) {
            tree.push(map[node.id]);
        }
    });

    return tree;
});

// --- KPIS MEJORADOS ---
const statsList = computed(() => [
    { 
        label: 'TOTAL_NODOS', 
        value: String(categoriesList.value.length).padStart(2, '0'), 
        icon: Layers, 
        color: 'text-primary', 
        bg: 'bg-primary/10',
        border: 'border-primary/30'
    },
    { 
        label: 'ACTIVOS', 
        value: String(categoriesList.value.filter(c => c.is_active).length).padStart(2, '0'), 
        icon: Wifi, 
        color: 'text-cyan-500', 
        bg: 'bg-cyan-500/10',
        border: 'border-cyan-500/30'
    },
    { 
        label: 'RESTRICCIÓN +18', 
        value: String(categoriesList.value.filter(c => c.requires_age_check).length).padStart(2, '0'), 
        icon: ShieldAlert, 
        color: 'text-warning', 
        bg: 'bg-warning/10',
        border: 'border-warning/30'
    },
]);

// --- ACCIONES ---
const toggleExpand = (id) => {
    const index = expandedCategories.value.indexOf(id);
    if (index === -1) expandedCategories.value.push(id);
    else expandedCategories.value.splice(index, 1);
};

const deleteCategory = (category) => {
    if (confirm(`¿CONFIRMAR ELIMINACIÓN // "${category.name}"?`)) {
        router.delete(route('admin.categories.destroy', category.id), { preserveScroll: true });
    }
};

// Sincronización con Servidor
const updateFilters = debounce(() => {
    router.get(route('admin.categories.index'), { 
        search: search.value, 
        show_inactive: showInactive.value 
    }, { preserveState: true, replace: true, preserveScroll: true });
}, 300);

watch([search, showInactive], updateFilters);

const displayCode = (category) => {
    return category.external_code || category.id.substring(0, 8).toUpperCase();
};
</script>

<template>
    <AdminLayout>
        <div class="max-w-6xl mx-auto pb-24 px-4 sm:px-6">
            
            <!-- Header -->
            <div class="flex flex-col md:flex-row justify-between items-end gap-4 mb-8 border-b border-primary/30 pb-6 relative group/header">
                <!-- Efecto de escaneo -->
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-primary/5 to-transparent translate-x-[-100%] group-hover/header:translate-x-[100%] transition-transform duration-1000"></div>
                
                <div class="relative z-10">
                    <h1 class="text-3xl font-display font-black tracking-widest text-primary uppercase glitch-text drop-shadow-[0_0_12px_hsl(var(--primary)/0.6)] leading-none"
                        data-text="CATÁLOGO MAESTRO">
                        CATÁLOGO MAESTRO
                    </h1>
                    <p class="text-[10px] font-mono text-muted-foreground mt-1 flex items-center gap-2">
                        <Cpu :size="12" class="text-primary animate-pulse" />
                        GESTIÓN JERÁRQUICA DE CATEGORÍAS
                        <Terminal :size="12" class="text-primary animate-pulse" />
                    </p>
                </div>
                
                <div class="flex items-center gap-3 w-full md:w-auto relative z-10">
                    <!-- Search -->
                    <div class="relative group/search flex-1 md:w-64">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within/search:text-primary transition-colors" :size="16" />
                        <input v-model="search" 
                               type="text" 
                               placeholder="> BUSCAR EN CATÁLOGO..." 
                               class="w-full pl-10 pr-4 py-2.5 bg-background border border-border/50 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all">
                        <!-- Efecto de escritura -->
                        <div class="absolute right-3 top-1/2 -translate-y-1/2 w-1 h-4 bg-primary animate-pulse"></div>
                    </div>
                    
                    <!-- Toggle Inactivos -->
                    <button @click="showInactive = !showInactive" 
                            class="h-[42px] px-3 border border-border/50 transition-all relative group/toggle"
                            :class="showInactive ? 'border-cyan-500/30 bg-cyan-500/10' : 'hover:border-primary/30'">
                        <component :is="showInactive ? Eye : EyeOff" 
                                   :size="18" 
                                   :class="showInactive ? 'text-cyan-500' : 'text-muted-foreground group-hover/toggle:text-primary'" />
                        <!-- Esquinas decorativas -->
                        <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary opacity-0 group-hover/toggle:opacity-100"></span>
                        <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary opacity-0 group-hover/toggle:opacity-100"></span>
                        <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary opacity-0 group-hover/toggle:opacity-100"></span>
                        <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary opacity-0 group-hover/toggle:opacity-100"></span>
                    </button>
                </div>
            </div>

            <!-- Stats Dashboard -->
            <div class="grid grid-cols-3 gap-4 mb-8">
                <div v-for="(stat, index) in statsList" :key="index" 
                     class="border border-border/50 p-4 relative group/stat">
                    <div class="flex items-center gap-4">
                        <div :class="`p-3 ${stat.bg} ${stat.color}`">
                            <component :is="stat.icon" :size="20" />
                        </div>
                        <div>
                            <p class="text-[8px] font-mono font-bold uppercase text-muted-foreground">{{ stat.label }}</p>
                            <p class="text-2xl font-mono font-black text-foreground mt-1">{{ stat.value }}</p>
                        </div>
                    </div>
                    <!-- Esquinas -->
                    <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary/30"></span>
                    <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary/30"></span>
                    <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary/30"></span>
                    <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary/30"></span>
                </div>
            </div>

            <!-- Árbol de Categorías -->
            <div class="space-y-3">
                <TransitionGroup name="list">
                    <div v-for="parent in treeData" :key="parent.id" 
                         @mouseenter="hoveredCategory = parent.id"
                         @mouseleave="hoveredCategory = null"
                         class="border border-border/50 transition-all duration-500 relative group/parent overflow-hidden"
                         :class="{
                             'shadow-neon-primary border-primary/30': expandedCategories.includes(parent.id),
                             'hover:border-primary/30 hover:shadow-neon-primary': !expandedCategories.includes(parent.id)
                         }">
                        
                        <!-- Scanline superior -->
                        <div class="absolute top-0 left-0 w-full h-[2px] bg-gradient-to-r from-transparent via-primary to-transparent translate-x-[-100%] group-hover/parent:translate-x-[100%] transition-transform duration-1000"></div>
                        
                        <!-- Categoría Padre -->
                        <div class="p-4 flex items-center gap-4 cursor-pointer select-none relative"
                             @click="toggleExpand(parent.id)">
                            
                            <!-- Icono/Imagen -->
                            <div class="w-16 h-16 border border-primary/30 bg-background overflow-hidden shrink-0 relative group/image">
                                <img v-if="parent.image_url" 
                                     :src="parent.image_url" 
                                     class="w-full h-full object-cover"
                                     alt="Category Icon">
                                <div v-else class="w-full h-full flex items-center justify-center bg-primary/5">
                                    <Folder :size="24" class="text-primary/50" />
                                </div>
                                <!-- Overlay de estado inactivo -->
                                <div v-if="!parent.is_active" 
                                     class="absolute inset-0 bg-background/80 backdrop-blur-[1px] flex items-center justify-center">
                                    <WifiOff :size="16" class="text-destructive" />
                                </div>
                                <!-- Efecto de escaneo en imagen -->
                                <div class="absolute inset-0 bg-gradient-to-b from-transparent via-primary/10 to-transparent translate-y-[-100%] group-hover/image:translate-y-[100%] transition-transform duration-700"></div>
                            </div>

                            <!-- Información -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <h3 class="font-mono font-bold text-lg truncate leading-tight group-hover/parent:text-primary transition-colors">
                                        {{ parent.name }}
                                    </h3>

                                    <Star v-if="parent.is_featured" 
                                          :size="14" 
                                          class="text-warning fill-warning animate-pulse" />
                                    
                                    <!-- Tooltip de descripción -->
                                    <div v-if="parent.description" 
                                         class="relative group/desc cursor-help">
                                        <Info :size="14" class="text-muted-foreground hover:text-primary transition-colors" />
                                        <div class="absolute left-0 bottom-full mb-2 w-48 p-2 bg-background border border-primary/30 text-[8px] font-mono text-foreground shadow-neon-primary opacity-0 invisible group-hover/desc:opacity-100 group-hover/desc:visible transition-all duration-300 z-50">
                                            {{ parent.description }}
                                            <div class="absolute -bottom-1 left-4 w-2 h-2 rotate-45 bg-primary/30"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Badges -->
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-2 py-0.5 border border-primary/30 bg-primary/5 text-primary text-[8px] font-mono flex items-center gap-1">
                                        <GitBranch :size="10" />
                                        {{ parent.children.length }} SUBCATEGORÍAS
                                    </span>
                                    
                                    <span v-if="parent.external_code" 
                                          class="px-2 py-0.5 border border-border/50 text-muted-foreground text-[8px] font-mono">
                                        <Hash :size="10" class="inline mr-1" />
                                        {{ parent.external_code }}
                                    </span>
                                    
                                    <span v-if="parent.tax_classification" 
                                          class="px-2 py-0.5 border border-emerald-500/30 bg-emerald-500/5 text-emerald-500 text-[8px] font-mono flex items-center gap-1">
                                        <ReceiptText :size="10" />
                                        {{ parent.tax_classification }}
                                    </span>
                                    
                                    <span v-if="parent.requires_age_check" 
                                          class="px-2 py-0.5 border border-warning/30 bg-warning/5 text-warning text-[8px] font-mono flex items-center gap-1 font-bold">
                                        <ShieldAlert :size="10" />
                                        +18
                                    </span>
                                </div>
                            </div>

                            <!-- Expand/Collapse Icon -->
                            <ChevronDown :size="24" 
                                         class="text-muted-foreground transition-all duration-300 shrink-0" 
                                         :class="{
                                             'rotate-180 text-primary': expandedCategories.includes(parent.id),
                                             'group-hover/parent:text-primary': !expandedCategories.includes(parent.id)
                                         }"/>
                        </div>

                        <!-- Subcategorías -->
                        <div v-show="expandedCategories.includes(parent.id)" class="border-t border-primary/30">
                            
                            <!-- Acciones de Padre -->
                            <div v-if="can_manage" 
                                 class="grid grid-cols-3 border-b border-primary/30 bg-muted/5 text-[8px] font-mono uppercase tracking-wider divide-x divide-primary/30">
                                <Link :href="route('admin.categories.create', { parent: parent.id })" 
                                      class="py-2.5 flex justify-center items-center gap-2 hover:bg-primary/5 hover:text-primary transition-colors relative group/add">
                                    <Plus :size="12" /> AÑADIR HIJA
                                    <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary opacity-0 group-hover/add:opacity-100"></span>
                                    <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary opacity-0 group-hover/add:opacity-100"></span>
                                    <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary opacity-0 group-hover/add:opacity-100"></span>
                                    <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary opacity-0 group-hover/add:opacity-100"></span>
                                </Link>
                                <Link :href="route('admin.categories.edit', parent.id)" 
                                      class="py-2.5 flex justify-center items-center gap-2 hover:bg-background hover:text-primary transition-colors relative group/edit">
                                    <Settings :size="12" /> EDITAR
                                </Link>
                                <button @click.stop="deleteCategory(parent)" 
                                        class="py-2.5 flex justify-center items-center gap-2 text-destructive hover:bg-destructive/5 transition-colors relative group/delete">
                                    <Trash2 :size="12" /> ELIMINAR
                                </button>
                            </div>

                            <!-- Lista de Hijas -->
                            <div class="p-4 bg-background/50 space-y-2">
                                <div v-for="child in parent.children" :key="child.id" 
                                     @mouseenter="hoveredCategory = child.id"
                                     @mouseleave="hoveredCategory = null"
                                     class="flex items-center justify-between p-2 pr-3 border border-border/50 hover:border-primary/30 transition-all relative group/child ml-8">
                                    
                                    <!-- Línea conectora -->
                                    <div class="absolute -left-8 top-1/2 w-8 h-[2px] bg-primary/30"></div>
                                    <div class="absolute -left-8 top-1/2 w-2 h-2 bg-primary/50 rounded-full -translate-x-1/2"></div>
                                    
                                    <div class="flex items-center gap-3 min-w-0 flex-1">
                                        <!-- Icono Hijo -->
                                        <div class="w-10 h-10 border border-primary/30 bg-background overflow-hidden shrink-0 relative">
                                            <img v-if="child.image_url" 
                                                 :src="child.image_url" 
                                                 class="w-full h-full object-cover">
                                            <div v-else class="w-full h-full flex items-center justify-center bg-primary/5">
                                                <FolderOpen :size="16" class="text-primary/50" />
                                            </div>
                                            <div v-if="!child.is_active" 
                                                 class="absolute inset-0 bg-background/80 flex items-center justify-center">
                                                <WifiOff :size="12" class="text-destructive" />
                                            </div>
                                        </div>

                                        <!-- Info Hijo -->
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center gap-2">
                                                <p class="font-mono font-bold truncate text-sm group-hover/child:text-primary transition-colors"
                                                   :class="{'opacity-50': !child.is_active}">
                                                    {{ child.name }}
                                                </p>
                                                <span class="text-[7px] font-mono text-primary/50">
                                                    {{ child.external_code || 'S/C' }}
                                                </span>
                                                <span v-if="child.requires_age_check" 
                                                      class="text-[8px] font-black text-warning flex items-center">
                                                    <ShieldAlert :size="8" /> +18
                                                </span>
                                                <span v-if="child.tax_classification" 
                                                      class="text-[8px] text-emerald-500">
                                                    <ReceiptText :size="8" />
                                                </span>
                                            </div>
                                            <p v-if="child.external_code" 
                                               class="text-[8px] font-mono text-muted-foreground flex items-center gap-1">
                                                <Hash :size="8" /> {{ child.external_code }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Acciones Hijo -->
                                    <div v-if="can_manage" 
                                         class="flex items-center gap-1 opacity-0 group-hover/child:opacity-100 transition-opacity">
                                        <Link :href="route('admin.categories.edit', child.id)" 
                                              class="p-1.5 text-muted-foreground hover:text-primary hover:bg-primary/5 transition-colors border border-transparent hover:border-primary/30">
                                            <Settings :size="12" />
                                        </Link>
                                        <button @click="deleteCategory(child)" 
                                                class="p-1.5 text-muted-foreground hover:text-destructive hover:bg-destructive/5 transition-colors border border-transparent hover:border-destructive/30">
                                            <Trash2 :size="12" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Hover overlay -->
                        <div v-if="hoveredCategory === parent.id" 
                             class="absolute inset-0 border-2 border-primary/30 pointer-events-none"></div>
                    </div>
                </TransitionGroup>

                <!-- Estado Vacío -->
                <div v-if="treeData.length === 0 && !search" 
                     class="border border-dashed border-primary/30 p-12 text-center relative">
                    <div class="w-20 h-20 mx-auto mb-4 border-2 border-dashed border-primary/30 flex items-center justify-center">
                        <Layers :size="32" class="text-primary/30" />
                    </div>
                    <h3 class="text-lg font-mono font-bold text-foreground glitch-text" data-text="CATÁLOGO VACÍO">CATÁLOGO VACÍO</h3>
                    <p class="text-xs font-mono text-muted-foreground mt-2 max-w-xs mx-auto">
                        COMIENZA DEFINICIENDO LA ESTRUCTURA PRINCIPAL DEL CATÁLOGO.
                    </p>
                    <Link v-if="can_manage" 
                          :href="route('admin.categories.create')" 
                          class="mt-6 inline-block px-6 py-2 border border-primary text-primary text-[10px] font-mono hover:bg-primary hover:text-primary-foreground transition-all">
                        CREAR PRIMERA CATEGORÍA
                    </Link>
                </div>
                
                <div v-else-if="treeData.length === 0 && search" 
                     class="border border-dashed border-primary/30 p-8 text-center">
                    <p class="text-xs font-mono text-muted-foreground">
                        NO SE ENCONTRARON RESULTADOS PARA "<span class="text-primary">{{ search }}</span>"
                    </p>
                </div>
            </div>

            <!-- Botón flotante de creación -->
            <Teleport to="body">
                <Link v-if="can_manage" 
                      :href="route('admin.categories.create')" 
                      class="fixed bottom-8 right-8 z-[9999] group/create">
                    <div class="w-14 h-14 bg-primary text-primary-foreground border border-primary-foreground/50 shadow-neon-primary flex items-center justify-center relative overflow-hidden">
                        <Plus :size="24" class="group-hover/create:rotate-90 transition-transform duration-500 relative z-10" />
                        <!-- Efecto scan -->
                        <span class="absolute inset-0 bg-primary-foreground/10 translate-y-full group-hover/create:translate-y-0 transition-transform duration-500"></span>
                        <!-- Esquinas -->
                        <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary-foreground/50"></span>
                        <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary-foreground/50"></span>
                        <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary-foreground/50"></span>
                        <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary-foreground/50"></span>
                    </div>
                    <span class="sr-only">CREAR CATEGORÍA</span>
                </Link>
            </Teleport>

            <!-- Session ID -->
            <div class="mt-8 text-center">
                <p class="text-[8px] font-mono text-muted-foreground">
                    SESSION_ID // CATALOG_INDEX // {{ new Date().toISOString().slice(0,10) }}
                </p>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
/* Animaciones */
@keyframes scanline {
    0% { transform: translateY(-100%); }
    100% { transform: translateY(1000%); }
}

.animate-scanline {
    animation: scanline 8s linear infinite;
}

/* Efecto glitch */
.glitch-text {
    position: relative;
    animation: glitch-skew 4s infinite linear alternate-reverse;
}

.glitch-text::before,
.glitch-text::after {
    content: attr(data-text);
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0.8;
}

.glitch-text::before {
    color: #0ff;
    z-index: -1;
    animation: glitch-anim-1 0.4s infinite linear alternate-reverse;
}

.glitch-text::after {
    color: #f0f;
    z-index: -2;
    animation: glitch-anim-2 0.4s infinite linear alternate-reverse;
}

@keyframes glitch-skew {
    0% { transform: skew(0deg); }
    20% { transform: skew(0deg); }
    21% { transform: skew(2deg); }
    22% { transform: skew(0deg); }
    80% { transform: skew(0deg); }
    81% { transform: skew(-2deg); }
    82% { transform: skew(0deg); }
    100% { transform: skew(0deg); }
}

@keyframes glitch-anim-1 {
    0% { clip-path: inset(20% 0 30% 0); }
    20% { clip-path: inset(50% 0 10% 0); }
    40% { clip-path: inset(10% 0 60% 0); }
    60% { clip-path: inset(80% 0 5% 0); }
    80% { clip-path: inset(30% 0 40% 0); }
    100% { clip-path: inset(40% 0 20% 0); }
}

@keyframes glitch-anim-2 {
    0% { clip-path: inset(60% 0 10% 0); }
    20% { clip-path: inset(20% 0 50% 0); }
    40% { clip-path: inset(70% 0 5% 0); }
    60% { clip-path: inset(10% 0 70% 0); }
    80% { clip-path: inset(40% 0 30% 0); }
    100% { clip-path: inset(30% 0 40% 0); }
}

/* Sombras neón */
.shadow-neon-primary {
    box-shadow: 0 0 20px hsl(var(--primary) / 0.3);
}

/* Transiciones de lista */
.list-move,
.list-enter-active,
.list-leave-active {
    transition: all 0.3s ease;
}

.list-enter-from,
.list-leave-to {
    opacity: 0;
    transform: scale(0.98);
}

.list-leave-active {
    position: absolute;
    width: 100%;
}
</style>