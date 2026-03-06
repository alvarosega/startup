<script setup>
import { ref, watch, computed } from 'vue';
import { router, Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
    Search, Plus, ChevronDown, ChevronUp, Edit3, Trash2, 
    AlertTriangle, CheckCircle2, Filter, PackageSearch, Barcode,
    Layers, Tag, ImageIcon, Cpu, Terminal, Wifi, WifiOff,
    Box, DollarSign, Hash, Eye, EyeOff, GitBranch, Zap
} from 'lucide-vue-next';
import debounce from 'lodash/debounce';

const props = defineProps({
    products: Object,
    filters: Object,
    brands: Array,
    categories: Array
});

// --- ESTADO ---
const search = ref(props.filters.search || '');
const expandedRows = ref(new Set());
const hoveredProduct = ref(null);

// --- LÓGICA DE ACORDEÓN ---
const toggleRow = (id) => {
    if (expandedRows.value.has(id)) expandedRows.value.delete(id);
    else expandedRows.value.add(id);
};

// --- BÚSQUEDA REACTIVA ---
watch(search, debounce((value) => {
    router.get(route('admin.products.index'), { search: value }, { preserveState: true, replace: true });
}, 300));

const deleteProduct = (id) => {
    if (confirm('¿CONFIRMAR ELIMINACIÓN // PRODUCTO Y VARIANTES?')) {
        router.delete(route('admin.products.destroy', id));
    }
};

// --- UTILIDADES ---
const getProductCode = (id) => {
    return `PRD_${String(id).slice(-4).toUpperCase()}`;
};

const formatPrice = (price) => {
    return new Intl.NumberFormat('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(price);
};

// --- ESTADÍSTICAS ---
const stats = computed(() => {
    const total = props.products.data?.length || 0;
    const incomplete = props.products.data?.filter(p => p.skus_count === 0).length || 0;
    const healthy = total - incomplete;
    
    return [
        { label: 'TOTAL', value: String(total).padStart(2, '0'), icon: PackageSearch, color: 'text-primary', bg: 'bg-primary/10' },
        { label: 'SALUDABLE', value: String(healthy).padStart(2, '0'), icon: CheckCircle2, color: 'text-cyan-500', bg: 'bg-cyan-500/10' },
        { label: 'INCOMPLETOS', value: String(incomplete).padStart(2, '0'), icon: AlertTriangle, color: 'text-warning', bg: 'bg-warning/10' },
    ];
});
</script>

<template>
    <AdminLayout>
        <Head title="Catálogo de Productos" />

        <div class="max-w-7xl mx-auto space-y-6 pb-20 px-4 md:px-0">
            
            <!-- Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 border-b border-primary/30 pb-6 relative group/header">
                <!-- Efecto de escaneo -->
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-primary/5 to-transparent translate-x-[-100%] group-hover/header:translate-x-[100%] transition-transform duration-1000"></div>
                
                <div class="relative z-10">
                    <h1 class="text-3xl font-display font-black tracking-widest text-primary uppercase glitch-text drop-shadow-[0_0_12px_hsl(var(--primary)/0.6)] leading-none"
                        data-text="CATÁLOGO">
                        CATÁLOGO
                    </h1>
                    <p class="text-[10px] font-mono text-muted-foreground mt-1 flex items-center gap-2">
                        <Cpu :size="12" class="text-primary animate-pulse" />
                        GESTIÓN MAESTRO Y UNIDADES (SKU)
                        <Terminal :size="12" class="text-primary animate-pulse" />
                    </p>
                </div>
                
                <Link :href="route('admin.products.create')" 
                      class="px-6 py-3 bg-primary text-primary-foreground font-mono text-xs border border-primary/50 relative overflow-hidden group/btn">
                    <span class="flex items-center gap-2 relative z-10">
                        <Plus :size="16" /> NUEVO PRODUCTO
                    </span>
                    <!-- Efecto scan -->
                    <span class="absolute inset-0 bg-primary-foreground/10 translate-y-full group-hover/btn:translate-y-0 transition-transform duration-500"></span>
                    <!-- Esquinas -->
                    <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary-foreground/50"></span>
                    <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary-foreground/50"></span>
                    <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary-foreground/50"></span>
                    <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary-foreground/50"></span>
                </Link>
            </div>

            <!-- Stats Dashboard -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 bg-background border border-border/50 p-4">
                <!-- Search -->
                <div class="relative md:col-span-2 group/search">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within/search:text-primary transition-colors" :size="16" />
                    <input v-model="search" 
                           type="text" 
                           placeholder="> BUSCAR POR NOMBRE O EAN..." 
                           class="w-full pl-10 pr-4 py-3 bg-background border border-border/50 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all">
                    <!-- Efecto de escritura -->
                    <div class="absolute right-3 top-1/2 -translate-y-1/2 w-1 h-4 bg-primary animate-pulse"></div>
                </div>

                <!-- Filtro por estado -->
                <select class="w-full px-4 py-3 bg-background border border-border/50 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all"
                        @change="e => router.get(route('admin.products.index'), { status: e.target.value })">
                    <option value="">TODOS LOS ESTADOS</option>
                    <option value="incomplete">INCOMPLETOS (SIN SKUS)</option>
                    <option value="complete">SALUDABLE</option>
                </select>

                <!-- Botón Filtros Avanzados -->
                <button class="flex items-center justify-center gap-2 border border-border/50 hover:border-primary hover:text-primary transition-all text-[10px] font-mono group/filter relative">
                    <Filter :size="14" class="group-hover/filter:text-primary" /> AVANZADO
                    <!-- Esquinas -->
                    <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary opacity-0 group-hover/filter:opacity-100"></span>
                    <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary opacity-0 group-hover/filter:opacity-100"></span>
                    <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary opacity-0 group-hover/filter:opacity-100"></span>
                    <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary opacity-0 group-hover/filter:opacity-100"></span>
                </button>
            </div>

            <!-- Tabla de Productos -->
            <div class="border border-border/50 shadow-2xl overflow-hidden rounded-2xl relative group/table">
                <!-- Scanline superior -->
                <div class="absolute top-0 left-0 w-full h-[2px] bg-gradient-to-r from-transparent via-primary to-transparent translate-x-[-100%] group-hover/table:translate-x-[100%] transition-transform duration-1000"></div>
                
                <!-- Esquinas decorativas grandes -->
                <div class="absolute top-0 left-0 w-4 h-4 border-t-2 border-l-2 border-primary/30"></div>
                <div class="absolute top-0 right-0 w-4 h-4 border-t-2 border-r-2 border-primary/30"></div>
                <div class="absolute bottom-0 left-0 w-4 h-4 border-b-2 border-l-2 border-primary/30"></div>
                <div class="absolute bottom-0 right-0 w-4 h-4 border-b-2 border-r-2 border-primary/30"></div>

                <table class="w-full text-left border-collapse">
                    <thead class="bg-muted/10 border-b border-primary/30">
                        <tr class="text-[9px] font-mono font-bold uppercase tracking-widest text-primary">
                            <th class="px-6 py-4 w-12 text-center">SALUD</th>
                            <th class="px-6 py-4">PRODUCTO MAESTRO</th>
                            <th class="px-6 py-4">CATEGORÍA / MARCA</th>
                            <th class="px-6 py-4 text-center">VARIANTES</th>
                            <th class="px-6 py-4 text-right">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-primary/10">
                        <template v-for="product in products.data" :key="product.id">
                            <tr @mouseenter="hoveredProduct = product.id"
                                @mouseleave="hoveredProduct = null"
                                class="hover:bg-primary/5 transition-colors group/row relative"
                                :class="product.skus_count === 0 ? 'border-l-2 border-l-warning' : ''">
                                
                                <!-- Estado -->
                                <td class="px-6 py-4 text-center">
                                    <AlertTriangle v-if="product.skus_count === 0" 
                                                   class="text-warning mx-auto animate-pulse" 
                                                   :size="20" />
                                    <CheckCircle2 v-else class="text-cyan-500 mx-auto" :size="20" />
                                </td>
                                
                                <!-- Producto -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 border border-primary/30 bg-background flex items-center justify-center overflow-hidden shrink-0 relative group/image">
                                            <img v-if="product.image_url" 
                                                 :src="product.image_url" 
                                                 class="object-cover w-full h-full">
                                            <PackageSearch v-else :size="20" class="text-primary/50" />
                                            <!-- Efecto de escaneo -->
                                            <div class="absolute inset-0 bg-gradient-to-b from-transparent via-primary/10 to-transparent translate-y-[-100%] group-hover/image:translate-y-[100%] transition-transform duration-700"></div>
                                        </div>
                                        <div>
                                            <p class="text-sm font-mono font-bold text-foreground group-hover/row:text-primary transition-colors">
                                                {{ product.name }}
                                            </p>
                                            <div class="flex items-center gap-2 mt-1">
                                                <span class="text-[8px] font-mono text-primary/50">{{ getProductCode(product.id) }}</span>
                                                <span v-if="!product.is_active" 
                                                      class="text-[8px] font-mono text-destructive flex items-center gap-1">
                                                    <WifiOff :size="8" /> OFFLINE
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                
                                <!-- Categoría / Marca -->
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-1">
                                        <span class="text-[8px] font-mono font-bold uppercase flex items-center gap-1.5 px-2 py-1 border border-primary/30 bg-primary/5 w-fit">
                                            <Layers :size="10" class="text-primary"/> 
                                            {{ product.category?.name || '---' }}
                                        </span>
                                        <span class="text-[8px] font-mono font-bold text-muted-foreground uppercase flex items-center gap-1.5 px-2">
                                            <Tag :size="10" class="text-primary" /> 
                                            {{ product.brand?.name || '---' }}
                                        </span>
                                    </div>
                                </td>
                                
                                <!-- Variantes -->
                                <td class="px-6 py-4 text-center">
                                    <button @click="toggleRow(product.id)" 
                                            class="inline-flex items-center gap-2 px-3 py-1.5 border transition-all relative group/skus"
                                            :class="product.skus_count === 0 
                                                ? 'border-warning/30 bg-warning/5 text-warning hover:bg-warning/10' 
                                                : 'border-primary/30 bg-primary/5 text-primary hover:bg-primary/10'">
                                        <Box :size="12" />
                                        <span class="text-[9px] font-mono font-bold">{{ product.skus_count }} SKUs</span>
                                        <component :is="expandedRows.has(product.id) ? ChevronUp : ChevronDown" 
                                                   :size="12" 
                                                   class="transition-transform duration-300" />
                                        <!-- Esquinas -->
                                        <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary opacity-0 group-hover/skus:opacity-100"></span>
                                        <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary opacity-0 group-hover/skus:opacity-100"></span>
                                        <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary opacity-0 group-hover/skus:opacity-100"></span>
                                        <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary opacity-0 group-hover/skus:opacity-100"></span>
                                    </button>
                                </td>
                                
                                <!-- Acciones -->
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-1 opacity-100 lg:opacity-0 lg:group-hover/row:opacity-100 transition-opacity">
                                        <Link :href="route('admin.products.skus.create', product.id)" 
                                              class="p-2 text-primary hover:bg-primary/10 border border-transparent hover:border-primary/30 transition-all relative group/add"
                                              title="AÑADIR VARIANTES">
                                            <Plus :size="16" />
                                            <span class="absolute -top-8 left-1/2 -translate-x-1/2 text-[8px] font-mono text-primary opacity-0 group-hover/add:opacity-100 whitespace-nowrap">
                                                AÑADIR SKU
                                            </span>
                                        </Link>
                                        <Link :href="route('admin.products.edit', product.id)" 
                                              class="p-2 text-foreground hover:text-primary hover:bg-primary/10 border border-transparent hover:border-primary/30 transition-all relative group/edit">
                                            <Edit3 :size="16" />
                                            <span class="absolute -top-8 left-1/2 -translate-x-1/2 text-[8px] font-mono text-primary opacity-0 group-hover/edit:opacity-100">
                                                EDITAR
                                            </span>
                                        </Link>
                                        <button @click="deleteProduct(product.id)" 
                                                class="p-2 text-muted-foreground hover:text-destructive hover:bg-destructive/10 border border-transparent hover:border-destructive/30 transition-all relative group/delete">
                                            <Trash2 :size="16" />
                                            <span class="absolute -top-8 left-1/2 -translate-x-1/2 text-[8px] font-mono text-destructive opacity-0 group-hover/delete:opacity-100 whitespace-nowrap">
                                                ELIMINAR
                                            </span>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Fila expandida con variantes -->
                            <tr v-if="expandedRows.has(product.id)" class="bg-muted/5">
                                <td colspan="5" class="px-8 py-6">
                                    <div class="relative">
                                        <!-- Línea conectora vertical -->
                                        <div class="absolute left-0 top-0 bottom-0 w-[2px] bg-gradient-to-b from-primary via-primary/50 to-transparent"></div>
                                        
                                        <div class="ml-6">
                                            <!-- Mensaje si no hay SKUs -->
                                            <div v-if="product.skus.length === 0" 
                                                 class="border border-warning/30 bg-warning/5 p-4 flex items-center gap-3">
                                                <AlertTriangle :size="16" class="text-warning animate-pulse" />
                                                <span class="text-[10px] font-mono text-warning uppercase tracking-wider">
                                                    ESTE MAESTRO REQUIERE AL MENOS UNA VARIANTE OPERATIVA
                                                </span>
                                            </div>

                                            <!-- Grid de variantes -->
                                            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                                <div v-for="sku in product.skus" :key="sku.id" 
                                                     class="border border-primary/30 bg-background p-3 flex items-center justify-between group/sku hover:border-primary hover:shadow-neon-primary transition-all relative overflow-hidden">
                                                    
                                                    <!-- Scanline interna -->
                                                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-primary/10 to-transparent translate-x-[-100%] group-hover/sku:translate-x-[100%] transition-transform duration-700"></div>
                                                    
                                                    <div class="flex items-center gap-3 relative z-10">
                                                        <div class="w-10 h-10 border border-primary/30 bg-background overflow-hidden shrink-0">
                                                            <img v-if="sku.image_url" 
                                                                 :src="sku.image_url" 
                                                                 class="w-full h-full object-cover">
                                                            <div v-else class="w-full h-full flex items-center justify-center">
                                                                <ImageIcon :size="14" class="text-primary/50" />
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <p class="text-[10px] font-mono font-bold text-foreground group-hover/sku:text-primary transition-colors">
                                                                {{ sku.name }}
                                                            </p>
                                                            <div class="flex items-center gap-2 mt-1">
                                                                <span class="text-[7px] font-mono text-primary/50">{{ sku.code || 'SIN CÓDIGO EAN' }}</span>
                                                                <span class="text-[7px] font-mono flex items-center gap-1"
                                                                      :class="sku.stock > 0 ? 'text-cyan-500' : 'text-destructive'">
                                                                    <Box :size="8" /> {{ sku.stock || 0 }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="flex items-center gap-2 relative z-10">
                                                        <span class="text-xs font-mono font-bold text-cyan-500">${{ formatPrice(sku.price) }}</span>
                                                        <Link :href="route('admin.skus.edit', sku.id)" 
                                                              class="p-1 rounded-md border border-transparent text-muted-foreground hover:text-primary hover:border-primary/30 opacity-0 group-hover/sku:opacity-100 transition-all">
                                                            <Edit3 :size="12" />
                                                        </Link>
                                                    </div>

                                                    <!-- Esquinas -->
                                                    <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary/30"></span>
                                                    <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary/30"></span>
                                                    <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary/30"></span>
                                                    <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary/30"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>

                <!-- Paginación (si existe) -->
                <div v-if="products.links && products.links.length > 3" 
                     class="p-6 border-t border-primary/30 bg-background/80 backdrop-blur-sm flex flex-col md:flex-row items-center justify-between gap-4">
                    <p class="text-[8px] font-mono text-muted-foreground uppercase tracking-widest">
                        MOSTRANDO {{ products.from }} - {{ products.to }} DE {{ products.total }} RESULTADOS
                    </p>
                    <div class="flex gap-1">
                        <template v-for="(link, p) in products.links" :key="p">
                            <div v-if="link.url === null" 
                                 class="px-3 py-1.5 text-[10px] font-mono text-muted-foreground/50 border border-transparent"
                                 v-html="link.label"></div>
                            <Link v-else 
                                  :href="link.url" 
                                  class="px-3 py-1.5 text-[10px] font-mono border transition-all relative group/page"
                                  :class="link.active 
                                      ? 'border-primary bg-primary/10 text-primary shadow-neon-primary' 
                                      : 'border-transparent text-muted-foreground hover:border-primary/30 hover:text-primary'" 
                                  v-html="link.label" />
                        </template>
                    </div>
                </div>
            </div>

            <!-- Session ID -->
            <div class="text-center">
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
</style>