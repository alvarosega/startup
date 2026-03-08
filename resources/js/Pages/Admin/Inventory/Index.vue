<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { 
    Search, MapPin, PackageOpen, AlertTriangle, 
    CheckCircle2, XCircle, BarChart3, History, 
    Filter, X, Factory, Tag, Layers, FolderTree
} from 'lucide-vue-next';

const props = defineProps({ 
    stock: Object, 
    branches: Array,
    providers: Array,
    brands: Array,
    categories: Array,
    filters: Object 
});

// REGLA DOD v2.0: Desempaquetado Seguro para evitar crashes reactivos
const stockList = computed(() => props.stock?.data || props.stock || []);

// UI State
const showAdvancedFilters = ref(false);

// Reactive Filters
const f = ref({
    search: props.filters.search || '',
    branch_id: props.filters.branch_id || '',
    provider_id: props.filters.provider_id || '',
    brand_id: props.filters.brand_id || '',
    category_id: props.filters.category_id || '',
    status: props.filters.status || '',
});

// UX: Contar cuántos filtros avanzados están activos
const activeFiltersCount = computed(() => {
    let count = 0;
    if (f.value.branch_id) count++;
    if (f.value.provider_id) count++;
    if (f.value.brand_id) count++;
    if (f.value.category_id) count++;
    if (f.value.status) count++;
    return count;
});

// UX: Lógica de Agrupación Visual por Sucursal
const shouldShowBranchHeader = (index) => {
    const data = stockList.value;
    if (!data || !data[index]) return false;
    if (index === 0) return true;
    return data[index]?.branch_id !== data[index - 1]?.branch_id;
};

// Motor de Filtros
let searchTimeout = null;
const applyFilters = () => {
    router.get(route('admin.inventory.index'), f.value, { 
        preserveState: true, 
        replace: true,
        preserveScroll: true 
    });
};

const resetFilters = () => {
    f.value = { search: f.value.search, branch_id: '', provider_id: '', brand_id: '', category_id: '', status: '' };
    applyFilters();
};

// Observador con Debounce solo para el texto (Evita saturar la DB)
watch(() => f.value.search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 350); 
});

// Los selects (comboboxes) aplican la consulta inmediatamente
watch([
    () => f.value.branch_id, 
    () => f.value.provider_id, 
    () => f.value.brand_id, 
    () => f.value.category_id, 
    () => f.value.status
], applyFilters);

// Utilidades Visuales
const formatMoney = (val) => new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB' }).format(val);

const getStatusConfig = (status) => {
    const map = {
        'IN_STOCK': { icon: CheckCircle2, class: 'text-emerald-600 bg-emerald-500/10 border-emerald-500/30', text: 'ÓPTIMO' },
        'LOW_STOCK': { icon: AlertTriangle, class: 'text-amber-600 bg-amber-500/10 border-amber-500/30', text: 'BAJO' },
        'OUT_OF_STOCK': { icon: XCircle, class: 'text-red-600 bg-red-500/10 border-red-500/30', text: 'AGOTADO' }
    };
    return map[status] || map['OUT_OF_STOCK'];
};
</script>

<template>
    <AdminLayout>
        <div class="max-w-[1400px] mx-auto pb-20 px-4 sm:px-6 lg:px-8">
            
            <div class="flex flex-col md:flex-row justify-between items-end mb-6 gap-6 pt-6">
                <div>
                    <h1 class="text-3xl font-black tracking-tight text-foreground flex items-center gap-3">
                        <BarChart3 class="text-primary" :size="32" stroke-width="2.5"/>
                        Stock Consolidado
                    </h1>
                    <p class="text-xs text-muted-foreground uppercase font-black tracking-widest mt-1">
                        Disponibilidad en Tiempo Real (FEFO)
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                    <div class="relative w-full sm:w-80 group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none transition-colors group-focus-within:text-primary">
                            <Search :size="18" class="text-muted-foreground group-focus-within:text-primary transition-colors" />
                        </div>
                        <input v-model="f.search" type="text" placeholder="Buscar producto, SKU, EAN..." 
                               class="bg-card border-border border rounded-xl pl-10 pr-4 h-12 text-sm font-medium w-full focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm" />
                    </div>

                    <button @click="showAdvancedFilters = !showAdvancedFilters" 
                            class="h-12 px-5 rounded-xl border flex items-center justify-center gap-2 font-bold text-xs transition-all shadow-sm relative overflow-hidden"
                            :class="showAdvancedFilters ? 'bg-primary text-primary-foreground border-primary shadow-primary/20' : 'bg-card text-muted-foreground hover:bg-muted/80 hover:text-foreground'">
                        <Filter :size="16"/> 
                        <span>Avanzado</span>
                        <span v-if="activeFiltersCount > 0 && !showAdvancedFilters" 
                              class="absolute top-1 right-1 flex h-3 w-3 items-center justify-center rounded-full bg-red-500 text-[8px] text-white font-black animate-in zoom-in">
                            {{ activeFiltersCount }}
                        </span>
                    </button>
                </div>
            </div>

            <div v-if="showAdvancedFilters" class="card p-6 mb-8 bg-card border shadow-lg shadow-border/10 animate-in slide-in-from-top-4 fade-in duration-200 relative rounded-2xl">
                
                <div class="flex items-center justify-between mb-4 border-b border-border/50 pb-3">
                    <h3 class="text-xs font-black uppercase tracking-widest text-muted-foreground flex items-center gap-2">
                        <Filter :size="14"/> Opciones de Filtrado
                    </h3>
                    <button v-if="activeFiltersCount > 0" @click="resetFilters" class="text-[10px] font-black uppercase tracking-wider text-red-500 hover:text-red-700 bg-red-500/10 hover:bg-red-500/20 px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1.5">
                        <X :size="12" stroke-width="3"/> Limpiar ({{ activeFiltersCount }})
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5">
                    
                    <div v-if="branches && branches.length > 0" class="space-y-1.5">
                        <label class="text-[10px] font-bold uppercase text-muted-foreground flex items-center gap-1.5"><MapPin :size="12"/> Sucursal</label>
                        <select v-model="f.branch_id" class="form-input w-full h-11 text-xs font-medium bg-muted/30 border-border/60 rounded-xl focus:ring-primary/20">
                            <option value="">Todas las Sedes</option>
                            <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold uppercase text-muted-foreground flex items-center gap-1.5"><Activity :size="12"/> Estado del Stock</label>
                        <select v-model="f.status" class="form-input w-full h-11 text-xs font-medium bg-muted/30 border-border/60 rounded-xl focus:ring-primary/20">
                            <option value="">Todos los Estados</option>
                            <option value="OUT_OF_STOCK">🚨 Agotado (0)</option>
                            <option value="LOW_STOCK">⚠️ Stock Bajo (< 10)</option>
                            <option value="IN_STOCK">✅ Óptimo (10+)</option>
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold uppercase text-muted-foreground flex items-center gap-1.5"><FolderTree :size="12"/> Categoría</label>
                        <select v-model="f.category_id" class="form-input w-full h-11 text-xs font-medium bg-muted/30 border-border/60 rounded-xl focus:ring-primary/20">
                            <option value="">Todas</option>
                            <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold uppercase text-muted-foreground flex items-center gap-1.5"><Layers :size="12"/> Marca</label>
                        <select v-model="f.brand_id" class="form-input w-full h-11 text-xs font-medium bg-muted/30 border-border/60 rounded-xl focus:ring-primary/20">
                            <option value="">Todas</option>
                            <option v-for="b in brands" :key="b.id" :value="b.id">{{ b.name }}</option>
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold uppercase text-muted-foreground flex items-center gap-1.5"><Factory :size="12"/> Proveedor</label>
                        <select v-model="f.provider_id" class="form-input w-full h-11 text-xs font-medium bg-muted/30 border-border/60 rounded-xl focus:ring-primary/20">
                            <option value="">Todos</option>
                            <option v-for="p in providers" :key="p.id" :value="p.id">{{ p.commercial_name || p.company_name }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="bg-card border rounded-2xl shadow-sm overflow-hidden relative">
                <div class="overflow-x-auto scrollbar-thin scrollbar-thumb-border scrollbar-track-transparent">
                    <table class="w-full text-left border-collapse whitespace-nowrap">
                        
                        <thead>
                            <tr class="bg-muted/40 border-b border-border text-[10px] font-black text-muted-foreground uppercase tracking-widest">
                                <th class="py-4 px-6 w-16 text-center">Estado</th>
                                <th class="py-4 px-6">Identificación del Producto</th>
                                <th class="py-4 px-6 text-center">Costo Unit. (Rango)</th>
                                <th class="py-4 px-6 text-center border-l border-border/50 bg-muted/20">Físico Neto</th>
                                <th class="py-4 px-6 text-center bg-muted/20">Inmovilizado</th>
                                <th class="py-4 px-6 text-right border-l border-primary/20 bg-primary/5 text-primary">Disponible (Venta)</th>
                                <th class="py-4 px-6 w-16 text-center">Auditoría</th>
                            </tr>
                        </thead>
                        
                        <tbody class="divide-y divide-border/40">
                            <template v-if="stockList.length > 0">
                                <template v-for="(item, index) in stockList" :key="item.sku_id + item.branch_id">
                                    
                                    <tr v-if="shouldShowBranchHeader(index)" class="bg-muted/60">
                                        <td colspan="7" class="py-2.5 px-6 border-y border-border shadow-inner">
                                            <div class="flex items-center gap-2 font-black text-[11px] uppercase tracking-widest text-foreground">
                                                <div class="bg-primary/20 p-1 rounded"><MapPin :size="14" class="text-primary"/></div>
                                                {{ item.branch_name }}
                                            </div>
                                        </td>
                                    </tr>

                                    <tr class="hover:bg-muted/20 transition-all duration-150 group">
                                        
                                        <td class="py-4 px-6">
                                            <div class="mx-auto flex items-center justify-center w-9 h-9 rounded-xl border-2 transition-transform group-hover:scale-110"
                                                 :class="getStatusConfig(item.status).class"
                                                 :title="getStatusConfig(item.status).text">
                                                <component :is="getStatusConfig(item.status).icon" :size="18" stroke-width="2.5"/>
                                            </div>
                                        </td>

                                        <td class="py-4 px-6">
                                            <div class="flex flex-col">
                                                <p class="text-[13px] font-black text-foreground group-hover:text-primary transition-colors">
                                                    {{ item.product_name }} 
                                                    <span class="text-muted-foreground/50 mx-1">/</span> 
                                                    <span class="text-foreground/80 font-bold">{{ item.sku_name }}</span>
                                                </p>
                                                <div class="flex items-center gap-2 mt-1.5">
                                                    <span class="flex items-center gap-1 text-[9px] font-mono text-muted-foreground bg-muted px-1.5 py-0.5 rounded border border-border/60">
                                                        <Tag :size="10"/> EAN: {{ item.sku_code }}
                                                    </span>
                                                    <span class="text-[9px] font-black text-muted-foreground uppercase bg-muted/40 px-1.5 py-0.5 rounded">
                                                        {{ item.brand_name }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="py-4 px-6 text-center">
                                            <span class="text-[11px] font-mono font-bold text-muted-foreground bg-background border px-2 py-1 rounded-lg shadow-sm">
                                                Bs {{ item.cost_range }}
                                            </span>
                                        </td>

                                        <td class="py-4 px-6 text-center border-l border-border/50 bg-muted/5 group-hover:bg-muted/10 transition-colors">
                                            <span class="text-sm font-bold text-foreground">{{ item.total_quantity }}</span>
                                        </td>

                                        <td class="py-4 px-6 text-center bg-muted/5 group-hover:bg-muted/10 transition-colors">
                                            <span class="inline-flex items-center justify-center text-[10px] font-black px-2 py-1 rounded-full border min-w-[32px]"
                                                  :class="item.total_reserved > 0 ? 'bg-orange-500/10 text-orange-600 border-orange-500/30' : 'bg-background border-border/50 text-muted-foreground/50'">
                                                {{ item.total_reserved }}
                                            </span>
                                        </td>

                                        <td class="py-4 px-6 text-right border-l border-primary/10 bg-primary/5 group-hover:bg-primary/10 transition-colors">
                                            <span class="text-xl font-black tabular-nums tracking-tight"
                                                  :class="{
                                                      'text-primary': item.status === 'IN_STOCK',
                                                      'text-amber-600': item.status === 'LOW_STOCK',
                                                      'text-red-600': item.status === 'OUT_OF_STOCK'
                                                  }">
                                                {{ item.available_quantity }}
                                            </span>
                                        </td>

                                        <td class="py-4 px-6 text-center">
                                            <div class="flex justify-center">
                                                <Link :href="route('admin.inventory.kardex', item.sku_id)" 
                                                      class="btn btn-ghost w-9 h-9 p-0 rounded-xl text-muted-foreground hover:text-primary hover:bg-primary/10 hover:shadow-inner border border-transparent hover:border-primary/20 transition-all"
                                                      title="Auditar Kardex">
                                                    <History :size="18" stroke-width="2.5"/>
                                                </Link>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </template>

                            <tr v-else>
                                <td colspan="7" class="py-28 text-center bg-muted/10">
                                    <div class="flex flex-col items-center justify-center animate-in fade-in zoom-in duration-300">
                                        <div class="w-20 h-20 bg-muted rounded-full flex items-center justify-center mb-4 border border-border/50 shadow-inner">
                                            <PackageOpen :size="36" class="text-muted-foreground/40" stroke-width="1.5"/>
                                        </div>
                                        <p class="font-black text-foreground uppercase tracking-widest text-sm">Sin Existencias</p>
                                        <p class="text-xs font-medium text-muted-foreground mt-2 max-w-sm">
                                            No se encontraron SKUs que coincidan con los filtros aplicados. Intenta limpiar la búsqueda.
                                        </p>
                                        <button v-if="activeFiltersCount > 0 || f.search" @click="resetFilters" 
                                                class="mt-6 px-6 py-2 rounded-xl bg-background border shadow-sm text-xs font-bold hover:bg-muted transition-colors">
                                            Restablecer Filtros
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div v-if="stock.links && stockList.length > 0" class="mt-8 flex justify-center pb-8">
                <div class="bg-card border rounded-full px-6 py-2 shadow-sm flex items-center gap-3">
                    <p class="text-[10px] font-black text-muted-foreground uppercase tracking-widest">
                        Total en vista: <span class="text-foreground">{{ stock.meta?.total || 0 }}</span> registros
                    </p>
                </div>
                </div>

        </div>
    </AdminLayout>
</template>