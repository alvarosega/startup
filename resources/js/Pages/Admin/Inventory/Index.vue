<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import debounce from 'lodash/debounce';
import { 
    Search, Filter, Package, Box, Building2, 
    AlertTriangle, CheckCircle2, Cpu, Terminal, 
    Wifi, WifiOff, DollarSign, Hash, Layers,
    TrendingUp, TrendingDown, Eye, EyeOff, Zap,
    BarChart3, PieChart, Activity, Target
} from 'lucide-vue-next';

const props = defineProps({
    inventory: Object,
    branches: Array, 
    filters: Object
});

const search = ref(props.filters.search || '');
const branchId = ref(props.filters.branch_id || '');
const viewMode = ref('list'); // 'list' o 'grid'
const hoveredItem = ref(null);

const updateParams = debounce(() => {
    router.get(route('admin.inventory.index'), { 
        search: search.value, 
        branch_id: branchId.value 
    }, { preserveState: true, replace: true, preserveScroll: true });
}, 300);

watch([search, branchId], updateParams);

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('es-BO', { 
        style: 'currency', 
        currency: 'BOB',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount);
};

// Estadísticas de inventario
const stats = computed(() => {
    const items = props.inventory.data || [];
    const totalItems = items.length;
    const totalQuantity = items.reduce((sum, item) => sum + (item.total_quantity || 0), 0);
    const lowStock = items.filter(item => item.total_quantity <= 5).length;
    const mediumStock = items.filter(item => item.total_quantity > 5 && item.total_quantity <= 20).length;
    const healthyStock = items.filter(item => item.total_quantity > 20).length;
    const totalValue = items.reduce((sum, item) => sum + ((item.total_quantity || 0) * (item.avg_cost || 0)), 0);
    
    return [
        { 
            label: 'TOTAL_ITEMS', 
            value: String(totalItems).padStart(2, '0'), 
            icon: Package, 
            color: 'text-primary', 
            bg: 'bg-primary/10' 
        },
        { 
            label: 'UNIDADES', 
            value: String(totalQuantity).padStart(2, '0'), 
            icon: Box, 
            color: 'text-cyan-500', 
            bg: 'bg-cyan-500/10' 
        },
        { 
            label: 'VALOR_TOTAL', 
            value: formatCurrency(totalValue), 
            icon: DollarSign, 
            color: 'text-emerald-500', 
            bg: 'bg-emerald-500/10',
            isCurrency: true 
        },
        { 
            label: 'STOCK_CRÍTICO', 
            value: String(lowStock).padStart(2, '0'), 
            icon: AlertTriangle, 
            color: 'text-warning', 
            bg: 'bg-warning/10' 
        },
    ];
});

// Determinar estado de stock
const getStockStatus = (quantity) => {
    if (quantity <= 5) {
        return {
            color: 'bg-warning',
            textColor: 'text-warning',
            borderColor: 'border-warning/30',
            shadow: 'shadow-[0_0_15px_rgba(245,158,11,0.3)]',
            icon: AlertTriangle,
            label: 'CRÍTICO'
        };
    } else if (quantity <= 20) {
        return {
            color: 'bg-amber-500',
            textColor: 'text-amber-500',
            borderColor: 'border-amber-500/30',
            shadow: 'shadow-[0_0_15px_rgba(245,158,11,0.2)]',
            icon: Activity,
            label: 'MEDIO'
        };
    } else {
        return {
            color: 'bg-cyan-500',
            textColor: 'text-cyan-500',
            borderColor: 'border-cyan-500/30',
            shadow: 'shadow-[0_0_15px_rgba(0,255,255,0.2)]',
            icon: CheckCircle2,
            label: 'SALUDABLE'
        };
    }
};

// Código de item
const getItemCode = (id) => {
    return `INV_${String(id).slice(-4).toUpperCase()}`;
};
</script>

<template>
    <AdminLayout>
        <div class="max-w-7xl mx-auto space-y-6 pb-20 px-4 md:px-0">
            
            <!-- Header -->
            <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4 border-b border-primary/30 pb-6 relative group/header">
                <!-- Efecto de escaneo -->
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-primary/5 to-transparent translate-x-[-100%] group-hover/header:translate-x-[100%] transition-transform duration-1000"></div>
                
                <div class="relative z-10 w-full md:w-auto">
                    <h1 class="text-2xl font-display font-black tracking-widest text-primary uppercase glitch-text drop-shadow-[0_0_12px_hsl(var(--primary)/0.6)] leading-none"
                        data-text="KARDEX DE INVENTARIO">
                        KARDEX DE INVENTARIO
                    </h1>
                    <p class="text-[10px] font-mono text-muted-foreground mt-1 flex items-center gap-2">
                        <Cpu :size="12" class="text-primary animate-pulse" />
                        STOCK FÍSICO CONSOLIDADO
                        <Terminal :size="12" class="text-primary animate-pulse" />
                    </p>
                </div>
                
                <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto relative z-10">
                    <!-- Selector de Sucursal -->
                    <select v-if="branches.length > 1" v-model="branchId" 
                            class="px-4 py-2 bg-background border border-border/50 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all appearance-none">
                        <option value="">TODAS LAS SUCURSALES</option>
                        <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                    </select>
                    
                    <!-- Search -->
                    <div class="relative group/search">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within/search:text-primary transition-colors" :size="14" />
                        <input v-model="search" 
                               type="text" 
                               placeholder="> BUSCAR POR SKU O PRODUCTO..." 
                               class="w-full md:w-72 pl-10 pr-4 py-2 bg-background border border-border/50 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all">
                        <!-- Efecto de escritura -->
                        <div class="absolute right-3 top-1/2 -translate-y-1/2 w-1 h-4 bg-primary animate-pulse"></div>
                    </div>
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
                    <p class="text-xl font-mono font-bold text-foreground mt-2 truncate" :class="stat.isCurrency ? 'text-sm' : 'text-2xl'">
                        {{ stat.value }}
                    </p>
                    <p class="text-[10px] text-muted-foreground font-mono">{{ stat.label }}</p>
                    <!-- Esquinas -->
                    <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary/30"></span>
                    <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary/30"></span>
                    <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary/30"></span>
                    <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary/30"></span>
                </div>
            </div>

            <!-- View Toggle -->
            <div class="flex justify-end">
                <div class="flex gap-1 border border-border/50 p-1">
                    <button @click="viewMode = 'list'"
                            class="px-3 py-1 text-[10px] font-mono transition-all"
                            :class="viewMode === 'list' ? 'bg-primary/10 text-primary border-b border-primary' : 'text-muted-foreground'">
                        LISTA
                    </button>
                    <button @click="viewMode = 'grid'"
                            class="px-3 py-1 text-[10px] font-mono transition-all"
                            :class="viewMode === 'grid' ? 'bg-primary/10 text-primary border-b border-primary' : 'text-muted-foreground'">
                        GRID
                    </button>
                </div>
            </div>

            <!-- Lista de Inventario -->
            <div class="border border-border/50 shadow-2xl overflow-hidden relative group/table">
                <!-- Scanline superior -->
                <div class="absolute top-0 left-0 w-full h-[2px] bg-gradient-to-r from-transparent via-primary to-transparent translate-x-[-100%] group-hover/table:translate-x-[100%] transition-transform duration-1000"></div>
                
                <!-- Esquinas decorativas grandes -->
                <div class="absolute top-0 left-0 w-4 h-4 border-t-2 border-l-2 border-primary/30"></div>
                <div class="absolute top-0 right-0 w-4 h-4 border-t-2 border-r-2 border-primary/30"></div>
                <div class="absolute bottom-0 left-0 w-4 h-4 border-b-2 border-l-2 border-primary/30"></div>
                <div class="absolute bottom-0 right-0 w-4 h-4 border-b-2 border-r-2 border-primary/30"></div>

                <!-- Vista Lista -->
                <div v-if="viewMode === 'list'" class="divide-y divide-primary/10">
                    <div v-for="(item, index) in inventory.data" :key="index" 
                         @mouseenter="hoveredItem = index"
                         @mouseleave="hoveredItem = null"
                         class="relative hover:bg-primary/5 transition-all duration-300 group/item">
                        
                        <!-- Barra de estado lateral con gradiente -->
                        <div class="absolute left-0 top-0 bottom-0 w-1.5" 
                             :class="getStockStatus(item.total_quantity).color"
                             :style="{ boxShadow: getStockStatus(item.total_quantity).shadow }"></div>

                        <div class="pl-6 py-4 flex flex-col md:flex-row justify-between items-center relative">
                            
                            <!-- Información Principal -->
                            <div class="flex-1 w-full mb-4 md:mb-0">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="text-[8px] font-mono border border-primary/30 bg-primary/5 text-primary px-2 py-0.5">
                                        {{ item.branch_name }}
                                    </span>
                                    <span v-if="item.brand_name" 
                                          class="text-[8px] font-mono text-cyan-500 border border-cyan-500/30 bg-cyan-500/5 px-2 py-0.5 flex items-center gap-1">
                                        <Layers :size="8" /> {{ item.brand_name }}
                                    </span>
                                    <span class="text-[8px] font-mono text-primary/50">
                                        {{ getItemCode(item.id) }}
                                    </span>
                                </div>
                                
                                <h3 class="text-base font-mono font-bold text-foreground leading-tight mb-1 group-hover/item:text-primary transition-colors">
                                    {{ item.product_name }}
                                </h3>
                                
                                <div class="flex items-center gap-3 text-[10px] font-mono text-muted-foreground">
                                    <span class="text-primary/80">{{ item.sku_name }}</span>
                                    <span class="text-primary/30">/</span>
                                    <span class="border border-border/30 px-1.5 py-0.5">
                                        <Hash :size="8" class="inline mr-1" /> {{ item.sku_code || 'N/A' }}
                                    </span>
                                </div>
                            </div>

                            <!-- Métricas -->
                            <div class="w-full md:w-auto flex flex-col md:flex-row items-center gap-6 md:gap-10 py-2 px-4 border-t md:border-t-0 border-primary/10">
                                <!-- Costo Ponderado -->
                                <div class="flex flex-col items-end">
                                    <span class="text-[8px] font-mono text-primary/50 uppercase tracking-widest flex items-center gap-1">
                                        <TrendingDown :size="8" /> COSTO POND.
                                    </span>
                                    <span class="text-sm font-mono font-bold text-emerald-500">
                                        {{ formatCurrency(item.avg_cost) }}
                                    </span>
                                </div>

                                <!-- Stock -->
                                <div class="flex flex-col items-end min-w-[100px]">
                                    <span class="text-[8px] font-mono text-primary/50 uppercase tracking-widest flex items-center gap-1">
                                        <Package :size="8" /> DISPONIBLE
                                    </span>
                                    <div class="flex items-baseline gap-1">
                                        <span class="text-3xl font-mono font-black text-foreground tabular-nums tracking-tighter"
                                              :class="getStockStatus(item.total_quantity).textColor">
                                            {{ item.total_quantity }}
                                        </span>
                                        <span class="text-[8px] font-mono text-primary/50">UNID.</span>
                                    </div>
                                    <div v-if="item.total_reserved > 0" 
                                         class="flex items-center gap-1 text-[8px] font-mono text-warning animate-pulse">
                                        <Activity :size="8" /> {{ item.total_reserved }} RESERVADOS
                                    </div>
                                </div>

                                <!-- Badge de estado -->
                                <div class="hidden lg:block">
                                    <span class="px-2 py-1 border text-[8px] font-mono"
                                          :class="getStockStatus(item.total_quantity).borderColor"
                                          :style="{ color: getStockStatus(item.total_quantity).textColor }">
                                        {{ getStockStatus(item.total_quantity).label }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Hover overlay -->
                        <div v-if="hoveredItem === index" 
                             class="absolute inset-0 border border-primary/30 pointer-events-none"></div>
                    </div>
                </div>

                <!-- Vista Grid -->
                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-6">
                    <div v-for="(item, index) in inventory.data" :key="index"
                         @mouseenter="hoveredItem = index"
                         @mouseleave="hoveredItem = null"
                         class="border border-border/50 p-4 relative group/item hover:border-primary/30 hover:shadow-neon-primary transition-all duration-500 overflow-hidden">
                        
                        <!-- Scanline interna -->
                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-primary/5 to-transparent translate-x-[-100%] group-hover/item:translate-x-[100%] transition-transform duration-700"></div>
                        
                        <!-- Barra superior de estado -->
                        <div class="absolute top-0 left-0 right-0 h-1" 
                             :class="getStockStatus(item.total_quantity).color"
                             :style="{ boxShadow: getStockStatus(item.total_quantity).shadow }"></div>
                        
                        <div class="relative z-10">
                            <!-- Header -->
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-[8px] font-mono border border-primary/30 bg-primary/5 text-primary px-2 py-0.5">
                                    {{ item.branch_name }}
                                </span>
                                <span class="text-[8px] font-mono text-primary/50">
                                    {{ getItemCode(item.id) }}
                                </span>
                            </div>

                            <!-- Producto -->
                            <h3 class="text-sm font-mono font-bold text-foreground mb-2 group-hover/item:text-primary transition-colors line-clamp-2">
                                {{ item.product_name }}
                            </h3>

                            <!-- SKU -->
                            <div class="flex items-center gap-2 mb-4 text-[9px] font-mono text-muted-foreground">
                                <span class="text-primary/80">{{ item.sku_name }}</span>
                                <span class="text-primary/30">|</span>
                                <span class="border border-border/30 px-1.5 py-0.5">
                                    {{ item.sku_code || 'N/A' }}
                                </span>
                            </div>

                            <!-- Métricas en Grid -->
                            <div class="grid grid-cols-2 gap-3 mt-4 pt-4 border-t border-primary/10">
                                <div>
                                    <span class="text-[7px] font-mono text-primary/50 uppercase block mb-1">COSTO</span>
                                    <span class="text-sm font-mono font-bold text-emerald-500">
                                        {{ formatCurrency(item.avg_cost) }}
                                    </span>
                                </div>
                                <div class="text-right">
                                    <span class="text-[7px] font-mono text-primary/50 uppercase block mb-1">STOCK</span>
                                    <div class="flex items-baseline justify-end gap-1">
                                        <span class="text-2xl font-mono font-black"
                                              :class="getStockStatus(item.total_quantity).textColor">
                                            {{ item.total_quantity }}
                                        </span>
                                        <span class="text-[7px] font-mono text-primary/50">U</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Reservados (si aplica) -->
                            <div v-if="item.total_reserved > 0" 
                                 class="mt-2 flex items-center gap-1 text-[7px] font-mono text-warning animate-pulse">
                                <Activity :size="8" /> {{ item.total_reserved }} RESERVADOS
                            </div>

                            <!-- Badge de estado -->
                            <div class="absolute top-2 right-2">
                                <span class="px-2 py-1 border text-[7px] font-mono"
                                      :class="getStockStatus(item.total_quantity).borderColor"
                                      :style="{ color: getStockStatus(item.total_quantity).textColor }">
                                    {{ getStockStatus(item.total_quantity).label }}
                                </span>
                            </div>
                        </div>

                        <!-- Esquinas decorativas -->
                        <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary/30"></span>
                        <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary/30"></span>
                        <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary/30"></span>
                        <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary/30"></span>
                    </div>
                </div>

                <!-- Estado vacío -->
                <div v-if="inventory.data.length === 0" 
                     class="flex flex-col items-center justify-center py-20 border border-dashed border-primary/30">
                    <div class="w-20 h-20 border-2 border-dashed border-primary/30 flex items-center justify-center mb-4">
                        <Package :size="32" class="text-primary/30" />
                    </div>
                    <h3 class="text-lg font-mono font-bold text-foreground glitch-text" data-text="SIN EXISTENCIAS">SIN EXISTENCIAS</h3>
                    <p class="text-xs font-mono text-muted-foreground mt-2">
                        AJUSTE LOS FILTROS PARA VER OTROS RESULTADOS.
                    </p>
                </div>
            </div>

            <!-- Paginación -->
            <div class="mt-8 flex justify-center items-center gap-4" v-if="inventory.prev_page_url || inventory.next_page_url">
                <Link v-if="inventory.prev_page_url" :href="inventory.prev_page_url" 
                      class="px-6 py-2 border border-border/50 text-[10px] font-mono font-bold uppercase hover:border-primary hover:text-primary transition-all relative group/prev">
                    <span class="flex items-center gap-2">
                        « ANTERIOR
                    </span>
                    <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary opacity-0 group-hover/prev:opacity-100"></span>
                    <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary opacity-0 group-hover/prev:opacity-100"></span>
                    <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary opacity-0 group-hover/prev:opacity-100"></span>
                    <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary opacity-0 group-hover/prev:opacity-100"></span>
                </Link>

                <div class="h-1 w-12 bg-primary/20 rounded-full"></div>

                <Link v-if="inventory.next_page_url" :href="inventory.next_page_url" 
                      class="px-6 py-2 border border-border/50 text-[10px] font-mono font-bold uppercase hover:border-primary hover:text-primary transition-all relative group/next">
                    <span class="flex items-center gap-2">
                        SIGUIENTE »
                    </span>
                    <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary opacity-0 group-hover/next:opacity-100"></span>
                    <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary opacity-0 group-hover/next:opacity-100"></span>
                    <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary opacity-0 group-hover/next:opacity-100"></span>
                    <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary opacity-0 group-hover/next:opacity-100"></span>
                </Link>
            </div>

            <!-- Session ID -->
            <div class="text-center">
                <p class="text-[8px] font-mono text-muted-foreground">
                    SESSION_ID // INVENTORY_INDEX // {{ new Date().toISOString().slice(0,10) }}
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