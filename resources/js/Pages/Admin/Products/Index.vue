<script setup>
import { ref, watch, computed } from 'vue';
import { router, Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
    Search, Plus, ChevronDown, ChevronRight, Edit3, Trash2, 
    AlertTriangle, CheckCircle2, Filter, PackageSearch, Barcode,
    Layers, Tag, Cpu, Terminal, Wifi, WifiOff, GitBranch,
    Zap, Eye, EyeOff, Hash, Package, Box, DollarSign,
    Scale, ArrowRight, Shield, XCircle
} from 'lucide-vue-next';
import debounce from 'lodash/debounce';

const props = defineProps({
    products: Object,  // Resource Collection { data: [], links: [], meta: {} }
    filters: Object,   // Estado de filtros del backend
    options: Object,   // { brands: [], categories: [] }
    stats: Object      // Estadísticas globales del catálogo
});

// --- 1. DATA UNWRAPPING (Regla 3.C DoD v2.0) ---
// Extrae los datos de forma segura, evitando que vue crashee si la data es nula
const productsList = computed(() => props.products?.data || []);

// --- 2. ESTADO REACTIVO ---
const filtersForm = ref({
    search: props.filters?.search || '',
    brand: props.filters?.brand || '',
    category: props.filters?.category || '',
    status: props.filters?.status || ''
});

const expandedRows = ref(new Set());
const hoveredProduct = ref(null);
const viewMode = ref('table');

// --- 3. LÓGICA DE ACORDEÓN ---
const toggleRow = (id) => {
    if (expandedRows.value.has(id)) {
        expandedRows.value.delete(id);
    } else {
        expandedRows.value.add(id);
    }
};

// --- 4. MOTOR DE BÚSQUEDA Y FILTRADO (Alta Eficiencia) ---
watch(filtersForm, debounce((val) => {
    // Limpiamos nulos y vacíos para mantener la URL limpia y eficiente
    const cleanFilters = Object.fromEntries(Object.entries(val).filter(([_, v]) => v !== '' && v !== null));
    
    router.get(route('admin.products.index'), cleanFilters, { 
        preserveState: true, 
        replace: true,
        preserveScroll: true
    });
}, 400), { deep: true });

const resetFilters = () => {
    filtersForm.value = { search: '', brand: '', category: '', status: '' };
};

// --- 5. ACCIONES ZERO-TRUST ---
const deleteProduct = (product) => {
    if (confirm(`¿CONFIRMAR ELIMINACIÓN DE MAESTRO Y TODAS SUS VARIANTES // ACTIVO: ${product.name.toUpperCase()}?`)) {
        router.delete(route('admin.products.destroy', product.id), { preserveScroll: true });
    }
};

// --- 6. UTILIDADES ---
const formatPrice = (price) => {
    return new Intl.NumberFormat('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(price || 0);
};

// --- 7. KPIS GLOBALES ---
const displayStats = computed(() => [
    { label: 'MAESTROS_TOTAL', value: props.stats?.total ?? 0, icon: Package, color: 'text-primary', bg: 'bg-primary/10' },
    { label: 'MAESTROS_ACTIVOS', value: props.stats?.active ?? 0, icon: CheckCircle2, color: 'text-cyan-500', bg: 'bg-cyan-500/10' },
    { label: 'REGLAS_SKU', value: props.stats?.total_skus ?? 0, icon: Hash, color: 'text-purple-500', bg: 'bg-purple-500/10' },
    { label: 'SIN_VARIANTES', value: props.stats?.incomplete ?? 0, icon: AlertTriangle, color: 'text-destructive', bg: 'bg-destructive/10' },
]);
</script>

<template>
    <AdminLayout>
        <Head title="Catálogo Maestro" />

        <div class="max-w-7xl mx-auto space-y-6 pb-20 px-4 md:px-0">
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 border-b border-primary/30 pb-6 relative group/header">
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-primary/5 to-transparent translate-x-[-100%] group-hover/header:translate-x-[100%] transition-transform duration-1000"></div>
                
                <div class="relative z-10">
                    <h1 class="text-3xl font-display font-black tracking-widest text-primary uppercase glitch-text drop-shadow-[0_0_12px_hsl(var(--primary)/0.6)] leading-none"
                        data-text="CATÁLOGO MAESTRO">
                        CATÁLOGO MAESTRO
                    </h1>
                    <p class="text-[10px] font-mono text-muted-foreground mt-2 flex items-center gap-2">
                        <Cpu :size="12" class="text-primary animate-pulse" />
                        CONTROL CENTRAL DE INVENTARIO Y VARIANTES
                        <Terminal :size="12" class="text-primary animate-pulse" />
                    </p>
                </div>
                
                <Link :href="route('admin.products.create')" 
                      class="px-6 py-3 bg-primary text-primary-foreground font-mono text-xs border border-primary/50 relative overflow-hidden group/btn shadow-neon-primary">
                    <span class="flex items-center gap-2 relative z-10 font-black">
                        <Plus :size="16" stroke-width="3" /> NUEVO PRODUCTO
                    </span>
                    <span class="absolute inset-0 bg-white/20 translate-y-full group-hover/btn:translate-y-0 transition-transform duration-300"></span>
                </Link>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div v-for="(stat, index) in displayStats" :key="index" 
                     class="border border-border/50 p-4 relative group/stat hover:border-primary/50 transition-all bg-background/50 backdrop-blur-sm">
                    <div class="flex items-center justify-between mb-2">
                        <div :class="`p-2 ${stat.bg} ${stat.color}`">
                            <component :is="stat.icon" :size="18" />
                        </div>
                        <span class="text-[8px] font-mono text-muted-foreground uppercase tracking-widest">{{ stat.label }}</span>
                    </div>
                    <p class="text-2xl font-mono font-black text-foreground">{{ String(stat.value).padStart(2, '0') }}</p>
                </div>
            </div>

            <div class="flex flex-col lg:flex-row gap-4 bg-background/50 border border-border/50 p-4 backdrop-blur-sm relative overflow-hidden">
                <div class="absolute top-0 left-0 w-1 h-full bg-primary"></div>
                
                <div class="relative flex-1 group/search">
                    <Search class="absolute left-4 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within/search:text-primary transition-colors" :size="16" />
                    <input v-model="filtersForm.search" type="text" placeholder="> BUSCAR POR MAESTRO O EAN DE VARIANTE..." 
                           class="w-full pl-12 pr-4 py-3 bg-background border border-border/50 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all uppercase text-foreground">
                </div>
                
                <select v-model="filtersForm.brand" class="bg-background border border-border/50 px-4 py-3 font-mono text-xs focus:border-primary outline-none uppercase appearance-none custom-select min-w-[160px]">
                    <option value="">-- MARCA --</option>
                    <option v-for="b in options?.brands" :key="b.id" :value="b.id">{{ b.name }}</option>
                </select>

                <select v-model="filtersForm.category" class="bg-background border border-border/50 px-4 py-3 font-mono text-xs focus:border-primary outline-none uppercase appearance-none custom-select min-w-[160px]">
                    <option value="">-- CATEGORÍA --</option>
                    <option v-for="c in options?.categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>

                <select v-model="filtersForm.status" class="bg-background border border-border/50 px-4 py-3 font-mono text-xs focus:border-primary outline-none uppercase appearance-none custom-select min-w-[160px]">
                    <option value="">-- ESTADO_SKU --</option>
                    <option value="complete">COMPLETOS (CON SKU)</option>
                    <option value="incomplete">INCOMPLETOS (SIN SKU)</option>
                </select>

                <button @click="resetFilters" class="px-6 py-3 border border-destructive/50 text-destructive hover:bg-destructive hover:text-white transition-all flex items-center justify-center gap-2 text-[10px] font-mono font-black uppercase group/clear">
                    <XCircle :size="14" class="group-hover/clear:rotate-90 transition-transform" /> LIMPIAR
                </button>
            </div>

            <div class="border border-border/50 shadow-2xl relative bg-card/30 backdrop-blur-sm">
                <div class="absolute top-0 left-0 w-4 h-4 border-t-2 border-l-2 border-primary/30"></div>
                <div class="absolute top-0 right-0 w-4 h-4 border-t-2 border-r-2 border-primary/30"></div>
                <div class="absolute bottom-0 left-0 w-4 h-4 border-b-2 border-l-2 border-primary/30"></div>
                <div class="absolute bottom-0 right-0 w-4 h-4 border-b-2 border-r-2 border-primary/30"></div>

                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-muted/20 border-b border-primary/30">
                            <tr class="text-[9px] font-mono font-bold uppercase tracking-widest text-primary">
                                <th class="px-6 py-5 w-12 text-center">ST</th>
                                <th class="px-6 py-5">PRODUCTO MAESTRO</th>
                                <th class="px-6 py-5">JERARQUÍA</th>
                                <th class="px-6 py-5 text-center">ESTRUCTURA (SKUS)</th>
                                <th class="px-6 py-5 text-right">OPERACIONES</th>
                            </tr>
                        </thead>
                        
                        <tbody class="divide-y divide-primary/10">
                            <tr v-if="productsList.length === 0">
                                <td colspan="5" class="py-20 text-center">
                                    <p class="text-xs font-mono text-primary/40 uppercase tracking-[0.5em] animate-pulse">
                                        [ NO_SE_ENCONTRARON_RESULTADOS ]
                                    </p>
                                </td>
                            </tr>

                            <template v-for="product in productsList" :key="product.id">
                                <tr @mouseenter="hoveredProduct = product.id"
                                    @mouseleave="hoveredProduct = null"
                                    class="hover:bg-primary/5 transition-colors group/row">
                                    
                                    <td class="px-6 py-4 text-center">
                                        <div class="w-2.5 h-2.5 rounded-full mx-auto shadow-neon-primary" 
                                             :class="!product.skus || product.skus.length === 0 ? 'bg-destructive animate-pulse shadow-[0_0_10px_#ef4444]' : 'bg-cyan-500 shadow-[0_0_10px_#06b6d4]'">
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-4">
                                            <div class="w-12 h-12 border border-primary/20 bg-black flex items-center justify-center overflow-hidden shrink-0 relative group/img">
                                                <img v-if="product.image_url" :src="product.image_url" class="object-contain w-full h-full p-1 transition-transform group-hover/img:scale-110">
                                                <div v-if="!product.is_active" class="absolute inset-0 bg-background/80 flex items-center justify-center">
                                                    <WifiOff :size="12" class="text-destructive" />
                                                </div>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-sm font-black text-foreground uppercase truncate group-hover/row:text-primary transition-colors">
                                                    {{ product.name }}
                                                </p>
                                                <div class="flex items-center gap-3 mt-1">
                                                    <span class="text-[8px] font-mono text-primary/50 tracking-widest">ID: {{ product.id.substring(0,8) }}...</span>
                                                    <span v-if="product.is_alcoholic" class="text-[8px] font-mono text-warning flex items-center gap-1 border border-warning/30 px-1 py-0.5">
                                                        <Wine :size="8" /> ETANOL_DETECTADO
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-4">
                                        <div class="space-y-1.5">
                                            <span class="text-[9px] font-mono font-bold uppercase flex items-center gap-2 text-muted-foreground">
                                                <Layers :size="12" class="text-primary/70" /> 
                                                {{ product.category?.name || 'S/C' }}
                                            </span>
                                            <span class="text-[9px] font-mono font-bold uppercase flex items-center gap-2 text-muted-foreground">
                                                <Tag :size="12" class="text-primary/70" /> 
                                                {{ product.brand?.name || 'S/B' }}
                                            </span>
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-4 text-center">
                                        <button @click="toggleRow(product.id)" 
                                                class="inline-flex items-center gap-2 px-4 py-2 border transition-all"
                                                :class="!product.skus || product.skus.length === 0 
                                                    ? 'border-destructive/30 bg-destructive/5 text-destructive hover:bg-destructive/10' 
                                                    : 'border-primary/30 bg-primary/5 text-primary hover:bg-primary/10 hover:shadow-neon-primary'">
                                            <Box :size="14" />
                                            <span class="text-[10px] font-mono font-black">{{ product.skus?.length || 0 }} SKUS</span>
                                            <component :is="expandedRows.has(product.id) ? ChevronDown : ChevronRight" :size="14" class="transition-transform duration-300" />
                                        </button>
                                    </td>
                                    
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <Link :href="route('admin.products.skus.create', product.id)" 
                                                  class="p-2 text-primary hover:bg-primary/10 border border-transparent hover:border-primary/30 transition-all" title="AÑADIR VARIANTE">
                                                <Plus :size="16" />
                                            </Link>
                                            <Link :href="route('admin.products.edit', product.id)" 
                                                  class="p-2 text-muted-foreground hover:text-primary hover:bg-primary/10 border border-transparent hover:border-primary/30 transition-all" title="EDITAR MAESTRO">
                                                <Edit3 :size="16" />
                                            </Link>
                                            <button @click="deleteProduct(product)" 
                                                    class="p-2 text-muted-foreground hover:text-destructive hover:bg-destructive/10 border border-transparent hover:border-destructive/30 transition-all" title="ELIMINAR">
                                                <Trash2 :size="16" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <tr v-if="expandedRows.has(product.id)" class="bg-black/20 animate-in fade-in duration-300">
                                    <td colspan="5" class="p-0 border-b border-primary/30">
                                        <div class="relative">
                                            <div class="absolute left-16 top-0 bottom-0 w-[2px] bg-gradient-to-b from-primary via-primary/30 to-transparent"></div>
                                            
                                            <div class="px-8 py-6 ml-12 border-l border-primary/20">
                                                
                                                <div v-if="!product.skus || product.skus.length === 0" 
                                                     class="border border-destructive/30 bg-destructive/5 p-4 flex items-center justify-between">
                                                    <div class="flex items-center gap-3">
                                                        <AlertTriangle :size="16" class="text-destructive animate-pulse" />
                                                        <span class="text-[10px] font-mono font-black text-destructive uppercase tracking-widest">
                                                            PRODUCTO INCOMPLETO // NO APTO PARA COMERCIALIZACIÓN
                                                        </span>
                                                    </div>
                                                    <Link :href="route('admin.products.skus.create', product.id)" class="text-[10px] font-mono font-black text-primary border border-primary/30 px-4 py-2 hover:bg-primary/10 transition-colors uppercase">
                                                        RESOLVER_INCIDENCIA ->
                                                    </Link>
                                                </div>

                                                <table v-else class="w-full text-left text-[10px] font-mono bg-background/50 border border-primary/10">
                                                    <thead class="text-[8px] font-mono font-black uppercase tracking-widest text-primary/60 border-b border-primary/20">
                                                        <tr>
                                                            <th class="py-3 px-4">VARIANTE_SKU</th>
                                                            <th class="py-3 px-4">CÓDIGO (EAN)</th>
                                                            <th class="py-3 px-4 text-center">FACTOR_PESO</th>
                                                            <th class="py-3 px-4 text-right">PRECIO_REF</th>
                                                            <th class="py-3 px-4 text-right">ACCIONES</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-primary/5">
                                                        <tr v-for="sku in product.skus" :key="sku.id" class="hover:bg-primary/5 transition-colors group/sku">
                                                            <td class="py-3 px-4">
                                                                <div class="flex items-center gap-3">
                                                                    <div class="w-6 h-6 border border-primary/20 bg-black flex items-center justify-center overflow-hidden">
                                                                        <img v-if="sku.image_url" :src="sku.image_url" class="object-cover w-full h-full">
                                                                        <Barcode v-else :size="12" class="text-primary/30" />
                                                                    </div>
                                                                    <span class="font-bold uppercase text-foreground group-hover/sku:text-primary transition-colors">
                                                                        {{ sku.name }}
                                                                    </span>
                                                                </div>
                                                            </td>
                                                            <td class="py-3 px-4 font-mono text-muted-foreground">
                                                                <span class="px-2 py-1 border border-primary/20 bg-primary/5 shadow-inner">
                                                                    {{ sku.code || 'SIN_ASIGNAR' }}
                                                                </span>
                                                            </td>
                                                            <td class="py-3 px-4 text-center font-mono text-muted-foreground">
                                                                <span class="flex items-center justify-center gap-2">
                                                                    <Scale :size="10" class="text-primary/70" /> {{ sku.conversion_factor }}x
                                                                    <span class="text-primary/30">|</span>
                                                                    <Hash :size="10" class="text-primary/70" /> {{ sku.weight }}kg
                                                                </span>
                                                            </td>
                                                            <td class="py-3 px-4 text-right font-mono font-black text-cyan-500">
                                                                ${{ formatPrice(sku.base_price) }}
                                                            </td>
                                                            <td class="py-3 px-4 text-right">
                                                                <Link :href="route('admin.skus.edit', sku.id)" class="inline-flex p-1.5 text-primary/50 hover:text-primary hover:bg-primary/10 border border-transparent hover:border-primary/30 transition-all">
                                                                    <Edit3 :size="12" />
                                                                </Link>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-if="props.products?.links && props.products.links.length > 3" class="mt-12 flex justify-center">
                <nav class="flex gap-2">
                    <template v-for="(link, k) in props.products.links" :key="k">
                        <Link v-if="link.url" :href="link.url" v-html="link.label" 
                              class="min-w-[45px] h-11 flex items-center justify-center text-[10px] font-mono font-black transition-all border"
                              :class="link.active ? 'bg-primary border-primary text-background shadow-neon-primary' : 'bg-background border-primary/20 text-primary/60 hover:border-primary hover:text-primary'"
                              :preserve-scroll="true" />
                        <span v-else v-html="link.label" class="min-w-[45px] h-11 flex items-center justify-center text-[10px] font-mono border border-transparent text-muted-foreground/20"></span>
                    </template>
                </nav>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
/* Aceleración de Hardware estricta para evitar la alerta de requestAnimationFrame (177ms) */
.glitch-text, .glitch-text::before, .glitch-text::after, .group\/row {
    will-change: transform, opacity, clip-path;
    backface-visibility: hidden;
}

.shadow-neon-primary { box-shadow: 0 0 15px hsl(var(--primary) / 0.3); }

.glitch-text { position: relative; }
.glitch-text::before, .glitch-text::after {
    content: attr(data-text);
    position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0.8;
}
.glitch-text::before { color: #0ff; z-index: -1; animation: glitch-anim-1 0.4s infinite linear alternate-reverse; }
.glitch-text::after { color: #f0f; z-index: -2; animation: glitch-anim-2 0.4s infinite linear alternate-reverse; }

@keyframes glitch-anim-1 {
    0% { clip-path: inset(20% 0 30% 0); transform: translate(-2px); }
    100% { clip-path: inset(40% 0 20% 0); transform: translate(2px); }
}
@keyframes glitch-anim-2 {
    0% { clip-path: inset(60% 0 10% 0); transform: translate(2px); }
    100% { clip-path: inset(30% 0 40% 0); transform: translate(-2px); }
}

.custom-select {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%234ade80' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 0.75rem center;
    background-repeat: no-repeat;
    background-size: 1.5em 1.5em;
    padding-right: 2.5rem;
}

.custom-scrollbar::-webkit-scrollbar { height: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: hsl(var(--border) / 0.2); }
.custom-scrollbar::-webkit-scrollbar-thumb { background: hsl(var(--primary) / 0.5); border-radius: 0; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: hsl(var(--primary)); }
</style>