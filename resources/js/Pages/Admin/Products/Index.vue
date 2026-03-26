<script setup>
import { ref, watch, computed, onMounted, onUnmounted } from 'vue';
import { router, Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
    Search, Plus, ChevronDown, ChevronRight, Edit3, Trash2, 
    AlertTriangle, CheckCircle2, Barcode, Layers, Tag, WifiOff, 
    Box, XCircle, Wine, Cpu, Terminal, ArrowLeft, ArrowRight, Loader2,
    PackagePlus
} from 'lucide-vue-next';
import debounce from 'lodash/debounce';

const props = defineProps({
    products: Object,   // CursorPaginator: { data: [], next_page_url, prev_page_url }
    filters: Object,    // Estado de filtros del backend
    options: Object,    // { brands: [], categories: [] }
    stats: Object,      // KPIs globales del catálogo
    can_manage: Boolean 
});

// --- 1. DATA MANAGEMENT ---
const productsList = computed(() => props.products?.data || []);
const isProcessing = ref(false);

// --- 2. ESTADO REACTIVO DE FILTROS ---
const filtersForm = ref({
    search: props.filters?.search || '',
    brand: props.filters?.brand || '',
    category: props.filters?.category || '',
    status: props.filters?.status || ''
});

// --- 3. MOTOR DE BÚSQUEDA Y FILTRADO (DEBOUNCED) ---
watch(filtersForm, debounce((val) => {
    isProcessing.value = true;
    router.get(route('admin.products.index'), 
        Object.fromEntries(Object.entries(val).filter(([_, v]) => v !== '' && v !== null)), 
        { 
            preserveState: true, 
            replace: true, 
            preserveScroll: true,
            onFinish: () => isProcessing.value = false
        }
    );
}, 400), { deep: true });

const resetFilters = () => {
    filtersForm.value = { search: '', brand: '', category: '', status: '' };
};

// --- 4. PROTOCOLO 4: FALSA INMEDIATEZ (AUTO-RELOAD STATS) ---
let statsInterval;
onMounted(() => {
    statsInterval = setInterval(() => {
        router.reload({ only: ['stats'], preserveScroll: true });
    }, 45000);
});
onUnmounted(() => clearInterval(statsInterval));

// --- 5. LÓGICA DE ACORDEÓN ---
const expandedRows = ref(new Set());
const toggleRow = (id) => {
    if (expandedRows.value.has(id)) expandedRows.value.delete(id);
    else expandedRows.value.add(id);
};

// --- 6. ACCIONES DE INTEGRIDAD ---
const deleteProduct = (product) => {
    if (confirm(`¿ELIMINAR MAESTRO: ${product.name.toUpperCase()}?\nESTA ACCIÓN ES IRREVERSIBLE Y BLOQUEARÁ EL NODO.`)) {
        isProcessing.value = true;
        router.delete(route('admin.products.destroy', product.id), { 
            preserveScroll: true,
            onFinish: () => isProcessing.value = false
        });
    }
};

const formatPrice = (price) => {
    return new Intl.NumberFormat('es-BO', { minimumFractionDigits: 2 }).format(price || 0);
};

// --- 7. KPIS GLOBALES ---
const displayStats = computed(() => [
    { label: 'MAESTROS_TOTAL', value: props.stats?.total ?? 0, icon: Box, color: 'text-primary', bg: 'bg-primary/10' },
    { label: 'SISTEMA_ACTIVO', value: props.stats?.active ?? 0, icon: CheckCircle2, color: 'text-cyan-500', bg: 'bg-cyan-500/10' },
    { label: 'NODOS_SKU', value: props.stats?.total_skus ?? 0, icon: Barcode, color: 'text-purple-500', bg: 'bg-purple-500/10' },
    { label: 'INCIDENCIAS', value: props.stats?.incomplete ?? 0, icon: AlertTriangle, color: 'text-destructive', bg: 'bg-destructive/10' },
]);
</script>

<template>
    <AdminLayout>
        <Head title="Control Maestro de Productos" />

        <div class="max-w-7xl mx-auto space-y-6 pb-24 px-4 sm:px-6 relative">
            <div v-if="isProcessing" class="fixed inset-0 bg-background/20 backdrop-blur-[2px] z-[100] flex items-center justify-center cursor-wait">
                <Loader2 class="text-primary animate-spin" :size="48" />
            </div>
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 border-b border-primary/30 pb-6 relative group/header">
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
                      class="h-11 px-8 bg-primary text-background font-black text-xs uppercase tracking-widest flex items-center justify-center gap-2 hover:shadow-neon-primary transition-all relative overflow-hidden group/btn">
                    <Plus :size="18" stroke-width="3" /> REGISTRAR_ACTIVO
                </Link>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div v-for="(stat, index) in displayStats" :key="index" 
                     class="border border-primary/20 p-4 bg-background/50 backdrop-blur-sm group/stat hover:border-primary/50 transition-all relative overflow-hidden">
                    <div class="flex items-center justify-between mb-2">
                        <div :class="`p-2 ${stat.bg} ${stat.color} relative overflow-hidden`">
                            <component :is="stat.icon" :size="18" />
                        </div>
                        <span class="text-[8px] font-mono font-black text-muted-foreground tracking-widest uppercase">{{ stat.label }}</span>
                    </div>
                    <p class="text-2xl font-mono font-black text-foreground">{{ String(stat.value).padStart(2, '0') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 bg-primary/5 p-4 border border-primary/20 backdrop-blur-sm relative">
                <div class="absolute left-0 top-0 w-1 h-full bg-primary"></div>
                
                <div class="relative group md:col-span-1">
                    <Search :size="14" class="absolute left-3 top-1/2 -translate-y-1/2 text-primary/40 group-focus-within:text-primary" />
                    <input v-model="filtersForm.search" type="text" placeholder="[ BUSCAR_MAESTRO ]" 
                           class="pl-9 w-full bg-background border border-primary/20 text-[10px] font-mono h-10 focus:border-primary outline-none text-foreground uppercase tracking-widest">
                </div>
                
                <select v-model="filtersForm.brand" class="bg-background border border-primary/20 text-[10px] font-mono h-10 px-3 focus:border-primary outline-none text-primary uppercase custom-select">
                    <option value="">-- MARCA --</option>
                    <option v-for="b in options?.brands" :key="b.id" :value="b.id">{{ b.name }}</option>
                </select>

                <select v-model="filtersForm.category" class="bg-background border border-primary/20 text-[10px] font-mono h-10 px-3 focus:border-primary outline-none text-primary uppercase custom-select">
                    <option value="">-- CATEGORÍA --</option>
                    <option v-for="c in options?.categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>

                <select v-model="filtersForm.status" class="bg-background border border-primary/20 text-[10px] font-mono h-10 px-3 focus:border-primary outline-none text-primary uppercase custom-select">
                    <option value="">-- ESTADO_SKU --</option>
                    <option value="complete">COMPLETO</option>
                    <option value="incomplete">INCOMPLETO</option>
                </select>

                <button @click="resetFilters" class="flex items-center justify-center gap-2 border border-destructive/50 text-destructive text-[9px] font-mono font-black hover:bg-destructive hover:text-white transition-all uppercase">
                    <XCircle :size="14" /> REINICIAR_SISTEMA
                </button>
            </div>

            <div class="border border-primary/10 bg-background/40 backdrop-blur-md overflow-hidden relative">
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
                        <template v-for="product in productsList" :key="product.id">
                            <tr class="hover:bg-primary/5 transition-all group/row">
                                <td class="px-6 py-4 text-center">
                                    <div class="w-2 h-2 rounded-full mx-auto" 
                                         :class="product.skus_count === 0 ? 'bg-destructive shadow-[0_0_8px_#ef4444] animate-pulse' : 'bg-primary shadow-[0_0_8px_#4ade80]'">
                                    </div>
                                </td>
                                
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 border border-primary/20 bg-black p-1 shrink-0 relative overflow-hidden">
                                            <img v-if="product.image_url" :src="product.image_url" class="w-full h-full object-contain">
                                            <WifiOff v-if="!product.is_active" class="absolute inset-0 m-auto text-destructive/50" :size="14" />
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-black text-foreground uppercase truncate group-hover/row:text-primary transition-colors">{{ product.name }}</p>
                                            <div class="flex items-center gap-2 mt-1">
                                                <span class="text-[8px] font-mono text-muted-foreground uppercase">ID: {{ product.id.split('-')[0] }}</span>
                                                <span v-if="product.is_alcoholic" class="text-[8px] font-mono text-warning flex items-center gap-1 border border-warning/30 px-1 py-0.5"><Wine :size="8" /> ETANOL</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="space-y-1">
                                        <div class="text-[9px] font-mono font-bold uppercase text-muted-foreground flex items-center gap-2"><Layers :size="10" /> {{ product.category?.name || 'S/C' }}</div>
                                        <div class="text-[9px] font-mono font-bold uppercase text-muted-foreground flex items-center gap-2"><Tag :size="10" /> {{ product.brand?.name || 'S/M' }}</div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <button @click="toggleRow(product.id)" 
                                            class="px-4 py-2 border border-primary/20 bg-primary/5 text-[10px] font-mono font-black transition-all hover:border-primary/40 hover:shadow-neon-primary"
                                            :class="product.skus_count === 0 ? 'text-destructive border-destructive/20' : 'text-primary'">
                                        {{ product.skus_count }} VARIANTES
                                        <component :is="expandedRows.has(product.id) ? ChevronDown : ChevronRight" :size="14" class="inline ml-1" />
                                    </button>
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2" v-if="can_manage">
                                        <Link :href="route('admin.products.skus.create', product.id)" 
                                              title="INYECTAR_SKU"
                                              class="p-2 text-primary hover:bg-primary/10 border border-transparent hover:border-primary/20 transition-all">
                                            <PackagePlus :size="16" />
                                        </Link>
                                        <Link :href="route('admin.products.edit', product.id)" 
                                              title="EDITAR_MAESTRO"
                                              class="p-2 text-muted-foreground hover:text-primary hover:bg-primary/10 border border-transparent hover:border-primary/20 transition-all">
                                            <Edit3 :size="16" />
                                        </Link>
                                        <button @click="deleteProduct(product)" 
                                                title="DESTRUIR_NODO"
                                                class="p-2 text-muted-foreground hover:text-destructive hover:bg-destructive/10 border border-transparent hover:border-destructive/20 transition-all">
                                            <Trash2 :size="16" />
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="expandedRows.has(product.id)" class="bg-black/40 border-l-4 border-primary animate-in fade-in slide-in-from-top-1 duration-300">
                                <td colspan="5" class="px-8 py-6">
                                    <div class="flex items-center justify-between mb-4 border-b border-primary/10 pb-2">
                                        <span class="text-[10px] font-mono text-primary/60 uppercase tracking-widest">[ SUB-SISTEMA_DE_VARIANTES ]</span>
                                        <Link v-if="can_manage" :href="route('admin.products.skus.create', product.id)" 
                                              class="text-[9px] font-mono text-cyan-500 hover:text-cyan-400 flex items-center gap-1 uppercase">
                                            <Plus :size="12" /> INYECTAR_NUEVA_VARIANTE
                                        </Link>
                                    </div>

                                    <div v-if="product.skus?.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                        <div v-for="sku in product.skus" :key="sku.id" 
                                             class="border border-primary/10 p-3 bg-background/60 hover:border-primary/30 transition-all flex gap-3 relative group/sku">
                                            <div class="w-12 h-12 border border-primary/20 bg-black shrink-0 relative overflow-hidden">
                                                <img v-if="sku.image_url" :src="sku.image_url" class="w-full h-full object-contain p-1">
                                                <WifiOff v-if="!sku.is_active" class="absolute inset-0 m-auto text-destructive/50" :size="12" />
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div class="flex justify-between items-start">
                                                    <p class="text-[10px] font-black uppercase truncate text-foreground group-hover/sku:text-primary transition-colors">{{ sku.name }}</p>
                                                    <Link v-if="can_manage" :href="route('admin.skus.edit', sku.id)" class="text-muted-foreground hover:text-primary"><Edit3 :size="10" /></Link>
                                                </div>
                                                <p class="text-[8px] font-mono text-primary/70 flex items-center gap-1 mt-0.5"><Barcode :size="10" /> {{ sku.code || 'S/C' }}</p>
                                                <div class="flex items-center justify-between mt-2">
                                                    <span class="text-xs font-mono font-black text-cyan-500">BS. {{ formatPrice(sku.base_price) }}</span>
                                                    <span class="text-[8px] font-mono text-muted-foreground uppercase">{{ sku.weight }}KG</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else class="text-center py-10 border border-dashed border-destructive/30 bg-destructive/5">
                                        <AlertTriangle class="mx-auto text-destructive mb-2" :size="20" />
                                        <p class="text-[10px] font-mono text-destructive uppercase tracking-widest">ALERTA: MAESTRO SIN DEFINICIÓN DE VARIANTES</p>
                                        <Link :href="route('admin.products.skus.create', product.id)" class="mt-4 inline-block px-4 py-2 bg-destructive text-white text-[9px] font-black uppercase">INICIALIZAR_SKUS</Link>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div class="flex justify-between items-center py-6 border-t border-primary/10">
                <span class="text-[9px] font-mono text-primary/40 uppercase">ORDENAMIENTO_NATURAL_UUIDv7_DESC</span>
                <div class="flex gap-4">
                    <Link v-if="props.products.prev_page_url" :href="props.products.prev_page_url" 
                          class="h-10 px-6 border border-primary/20 text-primary text-[10px] font-mono font-black flex items-center gap-2 hover:bg-primary/10 transition-all uppercase"
                          preserve-scroll>
                        <ArrowLeft :size="14" /> SECTOR_PREVIO
                    </Link>
                    <Link v-if="props.products.next_page_url" :href="props.products.next_page_url" 
                          class="h-10 px-6 border border-primary/20 text-primary text-[10px] font-mono font-black flex items-center gap-2 hover:bg-primary/10 transition-all uppercase"
                          preserve-scroll>
                        SECTOR_SIGUIENTE <ArrowRight :size="14" />
                    </Link>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.shadow-neon-primary { box-shadow: 0 0 15px hsl(var(--primary) / 0.3); }
.glitch-text { position: relative; }
.glitch-text::before { content: attr(data-text); position: absolute; left: -1px; text-shadow: 1px 0 #ff00c1; background: black; overflow: hidden; clip: rect(0,900px,0,0); animation: noise-1 2s infinite linear alternate-reverse; }
@keyframes noise-1 { 0% { clip: rect(20px,9999px,10px,0); } 100% { clip: rect(60px,9999px,80px,0); } }
.custom-select { background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%234ade80' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e"); background-position: right 0.75rem center; background-repeat: no-repeat; background-size: 1.5em 1.5em; padding-right: 2.5rem; appearance: none; }
</style>