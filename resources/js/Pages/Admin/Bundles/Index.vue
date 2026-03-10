<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Admin/Pagination.vue'; // <-- IMPORTACIÓN AÑADIDA
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
    const expired = props.bundles.data?.filter(b => b.ends_at && new Date(b.ends_at) < currentTime.value).length || 0;
    const fixedPrice = props.bundles.data?.filter(b => b.fixed_price).length || 0;
    
    return [
        { label: 'TOTAL_PACKS', value: String(total).padStart(2, '0'), icon: Package, color: 'text-primary', bg: 'bg-primary/10' },
        { label: 'ACTIVOS', value: String(active).padStart(2, '0'), icon: Wifi, color: 'text-cyan-500', bg: 'bg-cyan-500/10' },
        { label: 'VENCIDOS', value: String(expired).padStart(2, '0'), icon: Clock, color: 'text-destructive', bg: 'bg-destructive/10' },
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
            
            <div class="border-b border-primary/30 pb-6 relative group/header">
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

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div v-for="(stat, index) in stats" :key="index" 
                     class="border border-border/50 p-4 relative group/stat bg-background/50">
                    <div class="flex items-center justify-between">
                        <component :is="stat.icon" :size="20" :class="stat.color" />
                        <span class="text-[8px] font-mono text-primary/50">{{ stat.label }}</span>
                    </div>
                    <p class="text-2xl font-mono font-black text-foreground mt-2">{{ stat.value }}</p>
                    <p class="text-[10px] text-muted-foreground font-mono">{{ stat.label }}</p>
                    <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary/30"></span>
                    <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary/30"></span>
                    <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary/30"></span>
                    <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary/30"></span>
                </div>
            </div>

            <div class="sticky top-0 z-30 bg-background/95 backdrop-blur-md border border-primary/30 p-4 transition-all duration-300">
                <div class="flex flex-col md:flex-row gap-3">
                    <div class="relative flex-1 group/search">
                        <Search :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within/search:text-primary transition-colors" />
                        <input v-model="search" 
                               type="text" 
                               placeholder="[ BUSCAR_PACK ]" 
                               class="w-full pl-10 pr-4 py-3 bg-background border border-border/50 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all uppercase" />
                        <div class="absolute right-3 top-1/2 -translate-y-1/2 w-1 h-4 bg-primary animate-pulse"></div>
                    </div>
                    
                    <div class="relative w-full md:w-64 group/branch">
                        <MapPin :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within/branch:text-primary transition-colors" />
                        <select v-model="branchFilter" 
                                class="w-full pl-10 pr-8 py-3 bg-background border border-border/50 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all appearance-none cursor-pointer uppercase">
                            <option value="">TODAS LAS SUCURSALES</option>
                            <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                        </select>
                        <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-muted-foreground">
                            <svg width="10" height="6" viewBox="0 0 10 6" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 1L5 5L9 1"/></svg>
                        </div>
                    </div>
                </div>
            </div>

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

            <div v-if="bundles.data.length > 0" 
                 class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5"
                 :class="{ 'md:grid-cols-1 xl:grid-cols-1': viewMode === 'list' }">
                
                <div v-for="bundle in bundles.data" :key="bundle.id" 
                     @mouseenter="hoveredBundle = bundle.id"
                     @mouseleave="hoveredBundle = null"
                     class="border border-border/50 hover:border-primary/30 hover:shadow-neon-primary transition-all duration-500 relative group/card overflow-hidden bg-background/40"
                     :class="{ 'flex flex-row': viewMode === 'list' }">
                    
                    <div class="absolute top-0 left-0 w-full h-[2px] bg-gradient-to-r from-transparent via-primary to-transparent translate-x-[-100%] group-hover/card:translate-x-[100%] transition-transform duration-1000 z-20"></div>
                    
                    <span class="absolute top-0 left-0 w-2 h-2 border-t border-l border-primary/30 z-20"></span>
                    <span class="absolute top-0 right-0 w-2 h-2 border-t border-r border-primary/30 z-20"></span>
                    <span class="absolute bottom-0 left-0 w-2 h-2 border-b border-l border-primary/30 z-20"></span>
                    <span class="absolute bottom-0 right-0 w-2 h-2 border-b border-r border-primary/30 z-20"></span>

                    <div class="relative overflow-hidden bg-black border-r border-primary/20"
                         :class="viewMode === 'grid' ? 'aspect-video w-full border-b' : 'w-48 h-full shrink-0'">
                        <img v-if="bundle.image_url" 
                             :src="bundle.image_url"
                             class="w-full h-full object-cover transition-transform duration-700 group-hover/card:scale-105 opacity-80 group-hover/card:opacity-100" />
                        <div v-else class="w-full h-full flex flex-col items-center justify-center bg-primary/5">
                            <Package :size="viewMode === 'grid' ? 32 : 24" class="text-primary/30" />
                            <span class="text-[8px] font-mono text-primary/30 mt-1">SIN IMAGEN</span>
                        </div>

                        <div class="absolute top-2 left-2 flex flex-col gap-1 items-start z-10"> 
                            <span class="px-2 py-0.5 border text-[8px] font-mono backdrop-blur-sm shadow-md"
                                :class="bundle.is_active 
                                    ? 'border-cyan-500/30 bg-cyan-500/10 text-cyan-500' 
                                    : 'border-destructive/30 bg-destructive/10 text-destructive'">
                                <component :is="bundle.is_active ? Wifi : WifiOff" :size="8" class="inline mr-1" />
                                {{ bundle.is_active ? 'ACTIVO' : 'INACTIVO' }}
                            </span>
                        </div>

                        <div class="absolute bottom-2 right-2 z-10">
                            <span v-if="bundle.fixed_price" 
                                  class="px-2 py-1 border border-emerald-500/30 bg-emerald-500/10 text-emerald-500 text-[9px] font-mono font-black backdrop-blur-sm shadow-md">
                                <DollarSign :size="8" class="inline mr-1" />
                                {{ parseFloat(bundle.fixed_price).toFixed(2) }} BS
                            </span>
                            <span v-else 
                                  class="px-2 py-1 border border-primary/30 bg-primary/10 text-primary text-[8px] font-mono font-black backdrop-blur-sm shadow-md">
                                DINÁMICO
                            </span>
                        </div>
                    </div>
                    
                    <span v-if="getTimerStatus(bundle)" 
                        class="px-2 py-0.5 border text-[8px] font-mono backdrop-blur-sm flex items-center gap-1 z-10"
                        :class="[
                            getTimerStatus(bundle).color, getTimerStatus(bundle).border, getTimerStatus(bundle).bg,
                            viewMode === 'grid' ? 'absolute top-2 right-2' : 'ml-4 mt-2 self-start absolute right-2 top-2'
                        ]">
                        <Clock :size="8" />
                        {{ getTimerStatus(bundle).label }}
                    </span>
                    
                    <div class="p-5 flex-1 flex flex-col gap-3 relative z-10 w-full">
                        <div>
                            <div class="flex items-start justify-between gap-2 pr-16">
                                <h3 class="font-mono font-bold text-sm text-foreground leading-tight line-clamp-1 uppercase group-hover/card:text-primary transition-colors">
                                    {{ bundle.name }}
                                </h3>
                                <span class="text-[7px] font-mono text-primary/50 whitespace-nowrap">
                                    {{ getBundleCode(bundle.id) }}
                                </span>
                            </div>
                            
                            <div class="flex items-center gap-1.5 mt-1 text-[9px] font-mono text-muted-foreground uppercase">
                                <MapPin :size="10" class="text-primary" />
                                <span>{{ bundle.branch?.name || 'GLOBAL' }}</span>
                            </div>
                        </div>

                        <p class="text-[9px] font-mono text-muted-foreground line-clamp-2 h-8 uppercase">
                            {{ bundle.description || 'SIN DESCRIPCIÓN DISPONIBLE.' }}
                        </p>

                        <div class="mt-auto pt-3 border-t border-border/50">
                            <div class="flex items-center gap-2 mb-2">
                                <Gift :size="10" class="text-primary"/>
                                <span class="text-[7px] font-mono font-bold text-primary uppercase tracking-wider">CONTENIDO DEL PACK</span>
                            </div>
                            
                            <div class="flex flex-wrap gap-1.5">
                                <template v-if="bundle.items && bundle.items.length > 0">
                                    <div v-for="sku in bundle.items.slice(0, 3)" :key="sku.sku_id" 
                                         class="px-2 py-1 border border-primary/30 bg-primary/5 text-[8px] font-mono uppercase flex items-center gap-1 group/item">
                                        <Box :size="8" class="text-primary/50" />
                                        <span class="truncate max-w-[80px]">{{ sku.name }}</span>
                                        <span class="bg-primary/20 text-primary px-1 font-bold">x{{ sku.quantity }}</span>
                                    </div>
                                    <span v-if="bundle.items.length > 3" 
                                          class="px-2 py-1 border border-border/50 text-[8px] font-mono text-muted-foreground">
                                        +{{ bundle.items.length - 3 }}
                                    </span>
                                </template>
                                <span v-else class="text-[8px] font-mono text-warning flex items-center gap-1 uppercase">
                                    <AlertTriangle :size="8" /> SIN ITEMS ASIGNADOS
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 border-t border-border/50 divide-x divide-border/50 bg-background/50 backdrop-blur-sm w-full"
                         :class="{ 'absolute bottom-0 left-48 right-0 border-l': viewMode === 'list' }">
                        <Link :href="route('admin.bundles.edit', bundle.id)" 
                              class="flex items-center justify-center gap-2 py-3 text-[8px] font-mono font-bold uppercase tracking-wider text-muted-foreground hover:text-primary hover:bg-primary/5 transition-all relative group/edit">
                            <Edit :size="10" /> EDITAR
                            <span class="absolute inset-0 bg-primary/5 translate-y-full group-hover/edit:translate-y-0 transition-transform duration-300"></span>
                        </Link>
                        <button @click="deleteBundle(bundle.id)" 
                                class="flex items-center justify-center gap-2 py-3 text-[8px] font-mono font-bold uppercase tracking-wider text-muted-foreground hover:text-destructive hover:bg-destructive/5 transition-all relative group/delete">
                            <Trash2 :size="10" /> ELIMINAR
                            <span class="absolute inset-0 bg-destructive/5 translate-y-full group-hover/delete:translate-y-0 transition-transform duration-300"></span>
                        </button>
                    </div>

                    <div v-if="hoveredBundle === bundle.id" 
                         class="absolute inset-0 border-2 border-primary/30 pointer-events-none z-30"></div>
                </div>
            </div>

            <div v-else class="border border-dashed border-primary/30 p-12 text-center relative bg-primary/5">
                <div class="w-20 h-20 mx-auto mb-4 border-2 border-dashed border-primary/30 flex items-center justify-center">
                    <Package :size="32" class="text-primary/50" />
                </div>
                <h3 class="text-lg font-mono font-bold text-foreground glitch-text uppercase" data-text="RADAR VACÍO">
                    RADAR VACÍO
                </h3>
                <p class="text-[10px] font-mono text-muted-foreground mt-2 max-w-xs mx-auto uppercase">
                    {{ hasActiveFilters ? 'NO SE ENCONTRARON PACKS CON LOS PARÁMETROS ACTUALES.' : 'INICIALIZA LA BASE DE DATOS CREANDO EL PRIMER PACK PROMOCIONAL.' }}
                </p>
            </div>

            <Pagination 
                v-if="props.bundles?.meta && props.bundles.meta.last_page > 1" 
                :meta="props.bundles.meta" 
                class="mt-8"
            />

            <Teleport to="body">
                <Link :href="route('admin.bundles.create')" 
                      class="fixed bottom-8 right-8 z-[9999] group/create">
                    <div class="w-14 h-14 bg-primary text-primary-foreground border border-primary-foreground/50 shadow-neon-primary flex items-center justify-center relative overflow-hidden">
                        <Plus :size="24" class="group-hover/create:rotate-90 transition-transform duration-500 relative z-10" />
                        <span class="absolute inset-0 bg-primary-foreground/10 translate-y-full group-hover/create:translate-y-0 transition-transform duration-500"></span>
                        <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary-foreground/50"></span>
                        <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary-foreground/50"></span>
                        <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary-foreground/50"></span>
                        <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary-foreground/50"></span>
                    </div>
                </Link>
            </Teleport>

            <div class="mt-12 text-center opacity-30">
                <p class="text-[7px] font-mono text-muted-foreground uppercase tracking-[0.4em]">
                    BDL_IDX_NODE // v2.0 // TS: {{ new Date().toISOString() }}
                </p>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
/* Efecto glitch */
.glitch-text { position: relative; animation: glitch-skew 4s infinite linear alternate-reverse; }
.glitch-text::before, .glitch-text::after { content: attr(data-text); position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0.8; }
.glitch-text::before { color: #0ff; z-index: -1; animation: glitch-anim-1 0.4s infinite linear alternate-reverse; }
.glitch-text::after { color: #f0f; z-index: -2; animation: glitch-anim-2 0.4s infinite linear alternate-reverse; }
@keyframes glitch-skew { 0%, 20%, 22%, 80%, 82%, 100% { transform: skew(0deg); } 21% { transform: skew(2deg); } 81% { transform: skew(-2deg); } }
@keyframes glitch-anim-1 { 0% { clip-path: inset(20% 0 30% 0); } 100% { clip-path: inset(40% 0 20% 0); } }
@keyframes glitch-anim-2 { 0% { clip-path: inset(60% 0 10% 0); } 100% { clip-path: inset(30% 0 40% 0); } }

/* Sombras neón */
.shadow-neon-primary { box-shadow: 0 0 15px hsl(var(--primary) / 0.3); }

/* Ocultar scrollbar */
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>