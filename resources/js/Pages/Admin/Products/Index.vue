<script setup>
import { ref, watch, computed } from 'vue';
import { router, Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
    Search, Plus, ChevronDown, ChevronRight, Edit3, Trash2, 
    AlertTriangle, CheckCircle2, Barcode, Layers, Tag, WifiOff, 
    Hash, Package, Box, Scale, XCircle, Wine, Cpu, Terminal
} from 'lucide-vue-next';
import debounce from 'lodash/debounce';

const props = defineProps({
    products: Object,  // Resource Collection { data: [], links: [], meta: {} }
    filters: Object,   // Estado de filtros del backend
    options: Object,   // { brands: [], categories: [] }
    stats: Object,     // Estadísticas globales del catálogo
    can_manage: Boolean // LA LEY: Autorización desde el Backend
});

// --- 1. DATA UNWRAPPING (DoD v2.0) ---
const productsList = computed(() => props.products?.data || []);

// --- 2. ESTADO REACTIVO ---
const filtersForm = ref({
    search: props.filters?.search || '',
    brand: props.filters?.brand || '',
    category: props.filters?.category || '',
    status: props.filters?.status || ''
});

const expandedRows = ref(new Set());

// --- 3. LÓGICA DE ACORDEÓN ---
const toggleRow = (id) => {
    if (expandedRows.value.has(id)) expandedRows.value.delete(id);
    else expandedRows.value.add(id);
};

// --- 4. MOTOR DE BÚSQUEDA (Alta Eficiencia) ---
watch(filtersForm, debounce((val) => {
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
    if (confirm(`¿ELIMINAR MAESTRO: ${product.name.toUpperCase()}? // SE PERDERÁN TODAS LAS VARIANTES Y EL HISTORIAL DE PRECIOS.`)) {
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
    { label: 'SISTEMA_ACTIVO', value: props.stats?.active ?? 0, icon: CheckCircle2, color: 'text-cyan-500', bg: 'bg-cyan-500/10' },
    { label: 'NODOS_SKU', value: props.stats?.total_skus ?? 0, icon: Hash, color: 'text-purple-500', bg: 'bg-purple-500/10' },
    { label: 'INCIDENCIAS', value: props.stats?.incomplete ?? 0, icon: AlertTriangle, color: 'text-destructive', bg: 'bg-destructive/10' },
]);
</script>

<template>
    <AdminLayout>
        <Head title="Control Maestro de Productos" />

        <div class="max-w-7xl mx-auto space-y-6 pb-24 px-4 sm:px-6">
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 border-b border-primary/30 pb-6 relative group/header">
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-primary/5 to-transparent translate-x-[-100%] group-hover/header:translate-x-[100%] transition-transform duration-1000"></div>
                
                <div class="relative z-10">
                    <h1 class="text-3xl font-black text-primary uppercase tracking-tighter glitch-text" data-text="MAESTRO_DE_PRODUCTOS">
                        MAESTRO_DE_PRODUCTOS
                    </h1>
                    <p class="text-[10px] font-mono text-muted-foreground mt-1 flex items-center gap-2 uppercase">
                        <Cpu :size="12" class="text-primary animate-pulse" />
                        INDICE GLOBAL DE ACTIVOS COMERCIALES
                        <Terminal :size="12" class="text-primary animate-pulse" />
                    </p>
                </div>
                
                <Link v-if="can_manage" :href="route('admin.products.create')" 
                      class="h-11 px-8 bg-primary text-background font-black text-xs uppercase tracking-widest flex items-center justify-center gap-2 hover:bg-primary/90 transition-all shadow-neon-primary relative overflow-hidden group/btn">
                    <Plus :size="18" stroke-width="3" /> REGISTRAR_ACTIVO
                    <div class="absolute inset-0 bg-white/20 translate-y-full group-hover/btn:translate-y-0 transition-transform duration-300"></div>
                </Link>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div v-for="(stat, index) in displayStats" :key="index" 
                     class="border border-primary/20 p-4 bg-background/50 backdrop-blur-sm group/stat hover:border-primary/50 transition-all">
                    <div class="flex items-center justify-between mb-2">
                        <div :class="`p-2 ${stat.bg} ${stat.color} relative overflow-hidden`">
                            <component :is="stat.icon" :size="18" />
                            <div class="absolute inset-0 bg-current opacity-10 animate-pulse"></div>
                        </div>
                        <span class="text-[8px] font-mono font-black text-muted-foreground tracking-widest uppercase">{{ stat.label }}</span>
                    </div>
                    <p class="text-2xl font-mono font-black text-foreground">{{ String(stat.value).padStart(2, '0') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 bg-primary/5 p-4 border border-primary/20 backdrop-blur-sm relative overflow-hidden">
                <div class="absolute top-0 left-0 w-1 h-full bg-primary"></div>
                
                <div class="relative group md:col-span-1">
                    <Search :size="14" class="absolute left-3 top-1/2 -translate-y-1/2 text-primary/40 group-focus-within:text-primary" />
                    <input v-model="filtersForm.search" type="text" placeholder="[ BUSCAR_MAESTRO ]" 
                           class="pl-9 w-full bg-background border border-primary/20 text-[10px] font-mono h-10 focus:border-primary outline-none text-foreground uppercase">
                </div>
                
                <select v-model="filtersForm.brand" class="bg-background border border-primary/20 text-[10px] font-mono h-10 px-3 focus:border-primary outline-none text-primary uppercase cursor-pointer appearance-none custom-select">
                    <option value="">-- MARCA --</option>
                    <option v-for="b in options?.brands" :key="b.id" :value="b.id">{{ b.name }}</option>
                </select>

                <select v-model="filtersForm.category" class="bg-background border border-primary/20 text-[10px] font-mono h-10 px-3 focus:border-primary outline-none text-primary uppercase cursor-pointer appearance-none custom-select">
                    <option value="">-- CATEGORÍA --</option>
                    <option v-for="c in options?.categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>

                <select v-model="filtersForm.status" class="bg-background border border-primary/20 text-[10px] font-mono h-10 px-3 focus:border-primary outline-none text-primary uppercase cursor-pointer appearance-none custom-select">
                    <option value="">-- ESTADO_SKU --</option>
                    <option value="complete">COMPLETO</option>
                    <option value="incomplete">INCOMPLETO</option>
                </select>

                <button @click="resetFilters" class="flex items-center justify-center gap-2 border border-destructive/50 text-destructive text-[9px] font-mono font-black hover:bg-destructive hover:text-white transition-all uppercase">
                    <XCircle :size="14" /> REINICIAR_SISTEMA
                </button>
            </div>

            <div class="border border-primary/10 bg-background/40 backdrop-blur-md relative overflow-hidden">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-primary/5 border-b border-primary/20">
                            <tr class="text-[10px] font-mono font-black text-primary uppercase tracking-widest">
                                <th class="px-6 py-4 w-12 text-center">ST</th>
                                <th class="px-6 py-4">SISTEMA_MAESTRO</th>
                                <th class="px-6 py-4">JERARQUÍA</th>
                                <th class="px-6 py-4 text-center">ESTRUCTURA_SKU</th>
                                <th class="px-6 py-4 text-right">ACCIONES</th>
                            </tr>
                        </thead>
                        
                        <tbody class="divide-y divide-primary/10">
                            <tr v-if="productsList.length === 0">
                                <td colspan="5" class="py-20 text-center text-[10px] font-mono text-primary/40 uppercase tracking-[0.5em] animate-pulse">
                                    [ NO_SE_ENCONTRARON_ACTIVOS_EN_ESTE_SECTOR ]
                                </td>
                            </tr>

                            <template v-for="product in productsList" :key="product.id">
                                <tr class="hover:bg-primary/5 transition-all group/row border-l-2 border-transparent hover:border-primary">
                                    
                                    <td class="px-6 py-4 text-center">
                                        <div class="w-2 h-2 rounded-full mx-auto" 
                                             :class="!product.skus || product.skus.length === 0 ? 'bg-destructive shadow-[0_0_8px_#ef4444] animate-pulse' : 'bg-primary shadow-[0_0_8px_#4ade80]'">
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
                                                <div class="flex items-center gap-2 mt-1">
                                                    <span class="text-[8px] font-mono text-muted-foreground tracking-widest uppercase">ID: {{ product.id.substring(0,8) }}</span>
                                                    <span v-if="product.is_alcoholic" class="text-[8px] font-mono text-warning flex items-center gap-1 border border-warning/30 px-1 py-0.5">
                                                        <Wine :size="8" /> ETANOL
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-4">
                                        <div class="space-y-1">
                                            <div class="flex items-center gap-2 text-[9px] font-mono font-bold uppercase text-muted-foreground">
                                                <Layers :size="10" class="text-primary/60" /> {{ product.category?.name || 'S/C' }}
                                            </div>
                                            <div class="flex items-center gap-2 text-[9px] font-mono font-bold uppercase text-muted-foreground">
                                                <Tag :size="10" class="text-primary/60" /> {{ product.brand?.name || 'S/B' }}
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-4 text-center">
                                        <button @click="toggleRow(product.id)" 
                                                class="inline-flex items-center gap-2 px-4 py-2 border border-primary/20 bg-primary/5 text-[10px] font-mono font-black transition-all hover:bg-primary/10 hover:border-primary/40 hover:shadow-neon-primary"
                                                :class="!product.skus || product.skus.length === 0 ? 'text-destructive border-destructive/30' : 'text-primary'">
                                            <Box :size="14" />
                                            {{ product.skus?.length || 0 }} VARIANTES
                                            <component :is="expandedRows.has(product.id) ? ChevronDown : ChevronRight" :size="14" class="transition-transform" />
                                        </button>
                                    </td>
                                    
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end gap-2" v-if="can_manage">
                                            <Link :href="route('admin.products.skus.create', product.id)" class="p-2 text-muted-foreground hover:text-primary hover:bg-primary/10 border border-transparent hover:border-primary/20" title="AÑADIR_VARIANTE">
                                                <Plus :size="16" stroke-width="3" />
                                            </Link>
                                            <Link :href="route('admin.products.edit', product.id)" class="p-2 text-muted-foreground hover:text-primary hover:bg-primary/10 border border-transparent hover:border-primary/20" title="EDITAR">
                                                <Edit3 :size="16" />
                                            </Link>
                                            <button @click="deleteProduct(product)" class="p-2 text-muted-foreground hover:text-destructive hover:bg-destructive/10 border border-transparent hover:border-destructive/20" title="BORRAR">
                                                <Trash2 :size="16" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <tr v-if="expandedRows.has(product.id)" class="bg-black/20 animate-in fade-in slide-in-from-top-1 duration-300">
                                    <td colspan="5" class="p-0 border-b border-primary/30 relative">
                                        <div class="absolute left-16 top-0 bottom-0 w-[1px] bg-primary/30"></div>
                                        
                                        <div class="px-8 py-6 ml-16 border-l border-primary/20">
                                            <div v-if="!product.skus || product.skus.length === 0" class="flex items-center justify-between p-4 border border-dashed border-destructive/50 bg-destructive/5">
                                                <div class="flex items-center gap-3">
                                                    <AlertTriangle :size="18" class="text-destructive animate-pulse" />
                                                    <span class="text-[10px] font-mono font-black text-destructive uppercase tracking-widest">ERROR_LOGISTICO: MAESTRO SIN VARIANTES ACTIVAS</span>
                                                </div>
                                                <Link v-if="can_manage" :href="route('admin.products.skus.create', product.id)" class="px-4 py-2 border border-destructive/50 text-destructive text-[9px] font-mono font-black hover:bg-destructive hover:text-white transition-all uppercase">
                                                    INICIALIZAR_SKU
                                                </Link>
                                            </div>

                                            <table v-else class="w-full text-left text-[10px] font-mono border-collapse">
                                                <thead class="text-primary/60 border-b border-primary/10">
                                                    <tr class="uppercase font-black">
                                                        <th class="py-2 px-4">IDENTIDAD_SKU</th>
                                                        <th class="py-2 px-4">CODIGO_EAN</th>
                                                        <th class="py-2 px-4 text-center">FACTOR_PESO</th>
                                                        <th class="py-2 px-4 text-right">PRECIO_REF</th>
                                                        <th class="py-2 px-4 text-right" v-if="can_manage">ACCION</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-primary/5">
                                                    <tr v-for="sku in product.skus" :key="sku.id" class="hover:bg-primary/5 transition-colors group/sku">
                                                        <td class="py-3 px-4">
                                                            <div class="flex items-center gap-3">
                                                                <div class="w-8 h-8 border border-primary/10 bg-black flex items-center justify-center overflow-hidden shrink-0">
                                                                    <img v-if="sku.image_url" :src="sku.image_url" class="object-cover w-full h-full">
                                                                    <Barcode v-else :size="14" class="text-primary/20" />
                                                                </div>
                                                                <span class="font-bold uppercase text-foreground group-hover/sku:text-primary transition-colors">{{ sku.name }}</span>
                                                            </div>
                                                        </td>
                                                        <td class="py-3 px-4">
                                                            <span class="bg-primary/5 border border-primary/10 px-2 py-0.5 text-primary/70">
                                                                {{ sku.code || 'NO_SET' }}
                                                            </span>
                                                        </td>
                                                        <td class="py-3 px-4 text-center text-muted-foreground uppercase">
                                                            <Scale :size="10" class="inline mr-1" /> {{ sku.conversion_factor }}X | {{ sku.weight }}KG
                                                        </td>
                                                        <td class="py-3 px-4 text-right font-black text-cyan-500">
                                                            ${{ formatPrice(sku.base_price) }}
                                                        </td>
                                                        <td class="py-3 px-4 text-right" v-if="can_manage">
                                                            <Link :href="route('admin.skus.edit', sku.id)" class="p-1.5 text-muted-foreground hover:text-primary hover:bg-primary/10 border border-transparent hover:border-primary/20">
                                                                <Edit3 :size="14" />
                                                            </Link>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
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
                        <span v-else v-html="link.label" 
                              class="min-w-[45px] h-11 flex items-center justify-center text-[10px] font-mono border border-transparent text-muted-foreground/20"></span>
                    </template>
                </nav>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
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
.custom-scrollbar::-webkit-scrollbar-track { background: hsl(var(--border) / 0.1); }
.custom-scrollbar::-webkit-scrollbar-thumb { background: hsl(var(--primary) / 0.3); }
</style>