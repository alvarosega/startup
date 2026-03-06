<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
    Package, Plus, Edit, Trash2, Search, MapPin, 
    Layers, ShoppingBag, Tag, MoreHorizontal, AlertCircle,
    Cpu, Terminal, Wifi, WifiOff, Box, Gift, Zap,
    CheckCircle2, AlertTriangle, TrendingUp, DollarSign,
    Eye, EyeOff, GitBranch, Hash, Info, Clock
} from 'lucide-vue-next';
import { ref, watch, computed } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    bundles: Object,
    branches: Array
});
// --- LÓGICA DE TEMPORIZADOR (Rendimiento Extremo) ---
const currentTime = ref(new Date());
setInterval(() => {
    currentTime.value = new Date();
}, 60000); // Sincronización cada 60s es óptima para listas

const getTimerStatus = (bundle) => {
    if (!bundle.starts_at || !bundle.ends_at) return null;
    
    const start = new Date(bundle.starts_at);
    const end = new Date(bundle.ends_at);
    
    if (currentTime.value < start) return { label: 'PENDIENTE', color: 'text-warning', border: 'border-warning/30', bg: 'bg-warning/10' };
    if (currentTime.value > end) return { label: 'EXPIRADO', color: 'text-destructive', border: 'border-destructive/30', bg: 'bg-destructive/10' };
    
    // Calcular tiempo restante
    const diff = end - currentTime.value;
    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    const hours = Math.floor((diff / (1000 * 60 * 60)) % 24);
    
    return { 
        label: days > 0 ? `${days}D ${hours}H` : `${hours}H RESTANTES`, 
        color: 'text-primary', 
        border: 'border-primary/30', 
        bg: 'bg-primary/10' 
    };
};
const search = ref('');
const branchFilter = ref('');
const hoveredBundle = ref(null);
const viewMode = ref('grid'); // 'grid' o 'list'

// --- LÓGICA ORIGINAL ---
const updateParams = debounce(() => {
    router.get(route('admin.bundles.index'), { 
        search: search.value,
        branch_id: branchFilter.value 
    }, { preserveState: true, replace: true });
}, 300);

watch([search, branchFilter], updateParams);

const deleteBundle = (id) => {
    if (confirm('¿CONFIRMAR ELIMINACIÓN // ESTE PACK SERÁ ELIMINADO PERMANENTEMENTE?')) {
        router.delete(route('admin.bundles.destroy', id));
    }
};

const getImageUrl = (imagePath) => {
    if (!imagePath) return null;
    if (imagePath.startsWith('http')) return imagePath;
    return `/storage/${imagePath}`;
};

// --- HELPERS VISUALES ---
const hasActiveFilters = computed(() => search.value !== '' || branchFilter.value !== '');

const stats = computed(() => {
    const total = props.bundles.total || 0;
    const active = props.bundles.data?.filter(b => b.is_active).length || 0;
    // Nueva lógica: Contar expirados basados en la fecha actual
    const expired = props.bundles.data?.filter(b => b.ends_at && new Date(b.ends_at) < currentTime.value).length || 0;
    const fixedPrice = props.bundles.data?.filter(b => b.fixed_price).length || 0;
    
    return [
        { label: 'TOTAL_PACKS', value: String(total).padStart(2, '0'), icon: Package, color: 'text-primary', bg: 'bg-primary/10' },
        { label: 'ACTIVOS', value: String(active).padStart(2, '0'), icon: Wifi, color: 'text-cyan-500', bg: 'bg-cyan-500/10' },
        { label: 'VENCIDOS', value: String(expired).padStart(2, '0'), icon: Clock, color: 'text-destructive', bg: 'bg-destructive/10' }, // <--- CAMBIADO
        { label: 'PRECIO_FIJO', value: String(fixedPrice).padStart(2, '0'), icon: DollarSign, color: 'text-emerald-500', bg: 'bg-emerald-500/10' },
    ];
});
// Código de pack
const getBundleCode = (id) => {
    return `PCK_${String(id).slice(-4).toUpperCase()}`;
};
</script>

<template>
    <AdminLayout>
        <Head title="Gestión de Packs" />

        <div class="max-w-7xl mx-auto space-y-6 pb-20 px-4 md:px-0">
            
            <!-- Header -->
            <div class="border-b border-primary/30 pb-6 relative group/header">
                <!-- Efecto de escaneo -->
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-primary/5 to-transparent translate-x-[-100%] group-hover/header:translate-x-[100%] transition-transform duration-1000"></div>
                
                <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-display font-black tracking-widest text-primary uppercase glitch-text drop-shadow-[0_0_12px_hsl(var(--primary)/0.6)] leading-none"
                            data-text="PACKS & BUNDLES">
                            PACKS & BUNDLES
                        </h1>
                        <p class="text-[10px] font-mono text-muted-foreground mt-1 flex items-center gap-2">
                            <Cpu :size="12" class="text-primary animate-pulse" />
                            OFERTAS COMPUESTAS Y PROMOCIONES
                            <Terminal :size="12" class="text-primary animate-pulse" />
                        </p>
                    </div>
                    
                    <span class="text-xs font-mono font-bold text-primary border border-primary/30 px-2 py-1">
                        {{ String(bundles.total).padStart(2, '0') }} UNIDADES
                    </span>
                </div>
            </div>

            <!-- Stats Dashboard -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div v-for="(stat, index) in stats" :key="index" 
                     class="border border-border/50 p-4 relative group/stat">
                    <div class="flex items-center justify-between">
                        <component :is="stat.icon" :size="20" :class="stat.color" />
                        <span class="text-[8px] font-mono text-primary/50">{{ stat.label }}</span>
                    </div>
                    <p class="text-2xl font-mono font-bold text-foreground mt-2">{{ stat.value }}</p>
                    <p class="text-[10px] text-muted-foreground font-mono">{{ stat.label }}</p>
                    <!-- Esquinas -->
                    <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary/30"></span>
                    <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary/30"></span>
                    <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary/30"></span>
                    <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary/30"></span>
                </div>
            </div>

            <!-- Filtros y Búsqueda -->
            <div class="sticky top-0 z-30 bg-background/95 backdrop-blur-md border border-primary/30 p-4 transition-all duration-300">
                <div class="flex flex-col md:flex-row gap-3">
                    <!-- Search -->
                    <div class="relative flex-1 group/search">
                        <Search :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within/search:text-primary transition-colors" />
                        <input v-model="search" 
                               type="text" 
                               placeholder="> BUSCAR PACK..." 
                               class="w-full pl-10 pr-4 py-3 bg-background border border-border/50 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all" />
                        <!-- Efecto de escritura -->
                        <div class="absolute right-3 top-1/2 -translate-y-1/2 w-1 h-4 bg-primary animate-pulse"></div>
                    </div>
                    
                    <!-- Filtro por sucursal -->
                    <div class="relative w-full md:w-64 group/branch">
                        <MapPin :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within/branch:text-primary transition-colors" />
                        <select v-model="branchFilter" 
                                class="w-full pl-10 pr-8 py-3 bg-background border border-border/50 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all appearance-none cursor-pointer">
                            <option value="">TODAS LAS SUCURSALES</option>
                            <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                        </select>
                        <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-muted-foreground">
                            <svg width="10" height="6" viewBox="0 0 10 6" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 1L5 5L9 1"/></svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- View Toggle -->
            <div class="flex justify-end">
                <div class="flex gap-1 border border-border/50 p-1">
                    <button @click="viewMode = 'grid'"
                            class="px-3 py-1 text-[10px] font-mono transition-all"
                            :class="viewMode === 'grid' ? 'bg-primary/10 text-primary border-b border-primary' : 'text-muted-foreground'">
                        GRID
                    </button>
                    <button @click="viewMode = 'list'"
                            class="px-3 py-1 text-[10px] font-mono transition-all"
                            :class="viewMode === 'list' ? 'bg-primary/10 text-primary border-b border-primary' : 'text-muted-foreground'">
                        LISTA
                    </button>
                </div>
            </div>

            <!-- Grid de Packs -->
            <div v-if="bundles.data.length > 0" 
                 class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5"
                 :class="{ 'md:grid-cols-1 xl:grid-cols-1': viewMode === 'list' }">
                
                <div v-for="bundle in bundles.data" :key="bundle.id" 
                     @mouseenter="hoveredBundle = bundle.id"
                     @mouseleave="hoveredBundle = null"
                     class="border border-border/50 hover:border-primary/30 hover:shadow-neon-primary transition-all duration-500 relative group/card overflow-hidden"
                     :class="{ 'flex flex-row': viewMode === 'list' }">
                    
                    <!-- Scanline superior -->
                    <div class="absolute top-0 left-0 w-full h-[2px] bg-gradient-to-r from-transparent via-primary to-transparent translate-x-[-100%] group-hover/card:translate-x-[100%] transition-transform duration-1000"></div>
                    
                    <!-- Esquinas decorativas -->
                    <span class="absolute top-0 left-0 w-2 h-2 border-t border-l border-primary/30"></span>
                    <span class="absolute top-0 right-0 w-2 h-2 border-t border-r border-primary/30"></span>
                    <span class="absolute bottom-0 left-0 w-2 h-2 border-b border-l border-primary/30"></span>
                    <span class="absolute bottom-0 right-0 w-2 h-2 border-b border-r border-primary/30"></span>

                    <!-- Imagen (adaptable a grid/list) -->
                    <div class="relative overflow-hidden"
                         :class="viewMode === 'grid' ? 'aspect-video w-full' : 'w-48 h-full shrink-0'">
                        <img v-if="bundle.image_url" 
                             :src="bundle.image_url"
                             class="w-full h-full object-cover transition-transform duration-700 group-hover/card:scale-105" />
                        <div v-else class="w-full h-full flex flex-col items-center justify-center bg-primary/5">
                            <Package :size="viewMode === 'grid' ? 32 : 24" class="text-primary/30" />
                            <span class="text-[8px] font-mono text-primary/30 mt-1">SIN IMAGEN</span>
                        </div>

                        <!-- Badges superiores -->
                        <div class="absolute top-2 left-2 flex flex-col gap-1 items-start"> <span class="px-2 py-0.5 border text-[8px] font-mono backdrop-blur-sm"
                                :class="bundle.is_active 
                                    ? 'border-cyan-500/30 bg-cyan-500/10 text-cyan-500' 
                                    : 'border-destructive/30 bg-destructive/10 text-destructive'">
                                <component :is="bundle.is_active ? Wifi : WifiOff" :size="8" class="inline mr-1" />
                                {{ bundle.is_active ? 'ACTIVO' : 'INACTIVO' }}
                            </span>

                            <span v-if="getTimerStatus(bundle)" 
                                class="px-2 py-0.5 border text-[8px] font-mono backdrop-blur-sm flex items-center gap-1"
                                :class="[getTimerStatus(bundle).color, getTimerStatus(bundle).border, getTimerStatus(bundle).bg]">
                                <Clock :size="8" />
                                {{ getTimerStatus(bundle).label }}
                            </span>
                        </div>

                        <!-- Badge de precio -->
                        <div class="absolute bottom-2 right-2">
                            <span v-if="bundle.fixed_price" 
                                  class="px-2 py-1 border border-emerald-500/30 bg-emerald-500/10 text-emerald-500 text-[9px] font-mono backdrop-blur-sm">
                                <DollarSign :size="8" class="inline mr-1" />
                                {{ parseFloat(bundle.fixed_price).toFixed(2) }} BS
                            </span>
                            <span v-else 
                                  class="px-2 py-1 border border-primary/30 bg-primary/10 text-primary text-[8px] font-mono backdrop-blur-sm">
                                DINÁMICO
                            </span>
                        </div>
                    </div>
                    <span v-if="getTimerStatus(bundle)" 
                        class="px-2 py-0.5 border text-[8px] font-mono backdrop-blur-sm flex items-center gap-1"
                        :class="[getTimerStatus(bundle).color, getTimerStatus(bundle).border, getTimerStatus(bundle).bg]">
                        <Clock :size="8" />
                        {{ getTimerStatus(bundle).label }}
                    </span>
                    <!-- Contenido -->
                    <div class="p-5 flex-1 flex flex-col gap-3 relative z-10">
                        <!-- Header -->
                        <div>
                            <div class="flex items-start justify-between gap-2">
                                <h3 class="font-mono font-bold text-lg text-foreground leading-tight line-clamp-1 group-hover/card:text-primary transition-colors">
                                    {{ bundle.name }}
                                </h3>
                                <span class="text-[7px] font-mono text-primary/50 whitespace-nowrap">
                                    {{ getBundleCode(bundle.id) }}
                                </span>
                            </div>
                            
                            <div class="flex items-center gap-1.5 mt-1 text-[9px] font-mono text-muted-foreground">
                                <MapPin :size="10" class="text-primary" />
                                <span>{{ bundle.branch?.name || 'GLOBAL' }}</span>
                            </div>
                        </div>

                        <!-- Descripción -->
                        <p class="text-[9px] font-mono text-muted-foreground line-clamp-2 h-8">
                            {{ bundle.description || 'SIN DESCRIPCIÓN DISPONIBLE.' }}
                        </p>

                        <!-- Items del Pack -->
                        <div class="mt-auto pt-3 border-t border-primary/10">
                            <div class="flex items-center gap-2 mb-2">
                                <Gift :size="10" class="text-primary"/>
                                <span class="text-[7px] font-mono font-bold text-primary uppercase tracking-wider">CONTENIDO DEL PACK</span>
                            </div>
                            
                            <div class="flex flex-wrap gap-1.5">
                                <template v-if="bundle.items && bundle.items.length > 0">
                                    <div v-for="sku in bundle.items.slice(0, 3)" :key="sku.sku_id" 
                                         class="px-2 py-1 border border-primary/30 bg-primary/5 text-[8px] font-mono flex items-center gap-1 group/item">
                                        <Box :size="8" class="text-primary/50" />
                                        <span class="truncate max-w-[80px]">{{ sku.name }}</span>
                                        <span class="bg-primary/20 text-primary px-1 font-bold">x{{ sku.quantity }}</span>
                                    </div>
                                    <span v-if="bundle.items.length > 3" 
                                          class="px-2 py-1 border border-border/50 text-[8px] font-mono text-muted-foreground">
                                        +{{ bundle.items.length - 3 }}
                                    </span>
                                </template>
                                <span v-else class="text-[8px] font-mono text-warning flex items-center gap-1">
                                    <AlertTriangle :size="8" /> SIN ITEMS ASIGNADOS
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div class="grid grid-cols-2 border-t border-primary/10 divide-x divide-primary/10 bg-muted/5">
                        <Link :href="route('admin.bundles.edit', bundle.id)" 
                              class="flex items-center justify-center gap-2 py-3 text-[8px] font-mono uppercase tracking-wider text-muted-foreground hover:text-primary hover:bg-primary/5 transition-all relative group/edit">
                            <Edit :size="10" /> EDITAR
                            <span class="absolute inset-0 bg-primary/5 translate-y-full group-hover/edit:translate-y-0 transition-transform duration-300"></span>
                        </Link>
                        <button @click="deleteBundle(bundle.id)" 
                                class="flex items-center justify-center gap-2 py-3 text-[8px] font-mono uppercase tracking-wider text-muted-foreground hover:text-destructive hover:bg-destructive/5 transition-all relative group/delete">
                            <Trash2 :size="10" /> ELIMINAR
                            <span class="absolute inset-0 bg-destructive/5 translate-y-full group-hover/delete:translate-y-0 transition-transform duration-300"></span>
                        </button>
                    </div>

                    <!-- Hover overlay -->
                    <div v-if="hoveredBundle === bundle.id" 
                         class="absolute inset-0 border-2 border-primary/30 pointer-events-none"></div>
                </div>
            </div>

            <!-- Estado vacío -->
            <div v-else class="border border-dashed border-primary/30 p-12 text-center relative">
                <div class="w-20 h-20 mx-auto mb-4 border-2 border-dashed border-primary/30 flex items-center justify-center">
                    <Package :size="32" class="text-primary/30" />
                </div>
                <h3 class="text-lg font-mono font-bold text-foreground glitch-text" data-text="NO HAY PACKS ENCONTRADOS">
                    NO HAY PACKS ENCONTRADOS
                </h3>
                <p class="text-xs font-mono text-muted-foreground mt-2 max-w-xs mx-auto">
                    {{ hasActiveFilters ? 'INTENTA CAMBIAR LOS FILTROS DE BÚSQUEDA.' : 'CREA TU PRIMER PACK PROMOCIONAL.' }}
                </p>
            </div>

            <!-- Paginación -->
            <div v-if="bundles.links && bundles.links.length > 3" class="mt-8 flex justify-center">
                <div class="flex gap-1 overflow-x-auto max-w-full pb-2 no-scrollbar px-2">
                    <template v-for="(link, k) in bundles.links" :key="k">
                        <Link v-if="link.url" 
                              :href="link.url" 
                              v-html="link.label"
                              class="min-w-[36px] h-9 flex items-center justify-center text-[10px] font-mono border transition-all relative group/page"
                              :class="link.active 
                                  ? 'border-primary bg-primary/10 text-primary shadow-neon-primary' 
                                  : 'border-border/50 text-muted-foreground hover:border-primary/30 hover:text-primary'" />
                        <span v-else 
                              v-html="link.label" 
                              class="min-w-[36px] h-9 flex items-center justify-center text-[10px] text-muted-foreground/30 border border-transparent" />
                    </template>
                </div>
            </div>

            <!-- Botón flotante de creación -->
            <Teleport to="body">
                <Link :href="route('admin.bundles.create')" 
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
                    <span class="sr-only">NUEVO PACK</span>
                </Link>
            </Teleport>

            <!-- Session ID -->
            <div class="text-center">
                <p class="text-[8px] font-mono text-muted-foreground">
                    SESSION_ID // BUNDLES_INDEX // {{ new Date().toISOString().slice(0,10) }}
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

/* Ocultar scrollbar */
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>