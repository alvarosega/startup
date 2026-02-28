<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { 
    Search, MapPin, PackageOpen, AlertTriangle, 
    CheckCircle2, XCircle, BarChart3
} from 'lucide-vue-next';

const props = defineProps({ 
    stock: Object, 
    branches: Array,
    filters: Object 
});

const searchFilter = ref(props.filters.search || '');
const branchFilter = ref(props.filters.branch_id || '');

// Lógica de Agrupación Visual (Igual que en Compras)
const shouldShowBranchHeader = (index) => {
    const data = props.stock?.data;
    if (!data || !data[index]) return false;
    if (index === 0) return true;
    return data[index]?.branch_id !== data[index - 1]?.branch_id;
};

// Debounce manual simple para la búsqueda
let searchTimeout = null;
const applyFilters = () => {
    router.get(route('admin.inventory.index'), {
        search: searchFilter.value,
        branch_id: branchFilter.value,
    }, { 
        preserveState: true, 
        replace: true,
        preserveScroll: true 
    });
};

watch(searchFilter, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 400); // 400ms delay para no saturar el servidor
});

watch(branchFilter, applyFilters);

// Utilidades de Formato
const formatMoney = (val) => new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB' }).format(val);

const getStatusConfig = (status) => {
    const map = {
        'IN_STOCK': { icon: CheckCircle2, class: 'text-emerald-600 bg-emerald-500/10 border-emerald-500/20', text: 'ÓPTIMO' },
        'LOW_STOCK': { icon: AlertTriangle, class: 'text-amber-600 bg-amber-500/10 border-amber-500/20', text: 'BAJO' },
        'OUT_OF_STOCK': { icon: XCircle, class: 'text-red-600 bg-red-500/10 border-red-500/20', text: 'AGOTADO' }
    };
    return map[status] || map['OUT_OF_STOCK'];
};
</script>

<template>
    <AdminLayout>
        <div class="max-w-[1400px] mx-auto pb-20 px-4 sm:px-6 lg:px-8">
            
            <div class="flex flex-col md:flex-row justify-between items-end mb-8 gap-6 pt-6">
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-foreground flex items-center gap-3">
                        <BarChart3 class="text-primary" :size="28"/>
                        Stock Consolidado
                    </h1>
                    <p class="text-xs text-muted-foreground uppercase font-bold mt-1">Disponibilidad en tiempo real (FEFO)</p>
                </div>

                <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                    <div class="relative w-full sm:w-72">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <Search :size="16" class="text-muted-foreground" />
                        </div>
                        <input v-model="searchFilter" type="text" placeholder="Buscar producto, SKU o EAN..." 
                               class="bg-card border rounded-xl pl-10 pr-4 h-11 text-sm font-medium w-full focus:ring-2 focus:ring-primary/20 transition-all shadow-sm" />
                    </div>

                    <div v-if="branches && branches.length > 0" class="flex items-center bg-card border rounded-xl px-3 h-11 shadow-sm w-full sm:w-auto shrink-0">
                        <MapPin :size="16" class="text-muted-foreground mr-2"/>
                        <select v-model="branchFilter" class="bg-transparent border-none text-xs font-bold focus:ring-0 w-full cursor-pointer">
                            <option value="">Todas las Sedes</option>
                            <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="bg-card border rounded-2xl shadow-sm overflow-hidden relative">
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse whitespace-nowrap">
                        <thead>
                            <tr class="bg-muted/30 border-b border-border text-[10px] font-black text-muted-foreground uppercase tracking-widest">
                                <th class="py-4 px-6 w-12">Estado</th>
                                <th class="py-4 px-6">Identificación del Producto</th>
                                <th class="py-4 px-6 text-center">Costo Aprox.</th>
                                <th class="py-4 px-6 text-center border-l border-border/50 bg-muted/10">Físico</th>
                                <th class="py-4 px-6 text-center bg-muted/10">Reserva</th>
                                <th class="py-4 px-6 text-right border-l border-border/50 bg-primary/5 text-primary">Disponible</th>
                            </tr>
                        </thead>
                        
                        <tbody>
                            <template v-if="stock.data.length > 0">
                                <template v-for="(item, index) in stock.data" :key="item.sku_id + item.branch_id">
                                    
                                    <tr v-if="shouldShowBranchHeader(index)" class="bg-muted/50 border-y border-border">
                                        <td colspan="6" class="py-2 px-6">
                                            <div class="flex items-center gap-2 font-black text-[10px] uppercase tracking-widest text-muted-foreground">
                                                <MapPin :size="12" class="text-primary"/> {{ item.branch_name }}
                                            </div>
                                        </td>
                                    </tr>

                                    <tr class="border-b border-border/50 last:border-0 hover:bg-muted/10 transition-colors group">
                                        
                                        <td class="py-3 px-6">
                                            <div class="flex items-center justify-center w-8 h-8 rounded-lg border"
                                                 :class="getStatusConfig(item.status).class"
                                                 :title="getStatusConfig(item.status).text">
                                                <component :is="getStatusConfig(item.status).icon" :size="16" stroke-width="3"/>
                                            </div>
                                        </td>

                                        <td class="py-3 px-6">
                                            <p class="text-sm font-black text-foreground group-hover:text-primary transition-colors">
                                                {{ item.product_name }} <span class="text-xs font-bold text-muted-foreground ml-1">- {{ item.sku_name }}</span>
                                            </p>
                                            <div class="flex items-center gap-3 mt-1">
                                                <span class="text-[10px] font-mono text-muted-foreground bg-muted px-1.5 py-0.5 rounded">EAN: {{ item.sku_code }}</span>
                                                <span class="text-[10px] font-bold text-muted-foreground uppercase">{{ item.brand_name }}</span>
                                            </div>
                                        </td>

                                        <td class="py-3 px-6 text-center">
                                            <span class="text-xs font-mono font-medium text-muted-foreground bg-background border px-2 py-1 rounded-md">
                                                Bs {{ item.cost_range }}
                                            </span>
                                        </td>

                                        <td class="py-3 px-6 text-center border-l border-border/50 bg-muted/5">
                                            <span class="text-sm font-bold text-foreground">{{ item.total_quantity }}</span>
                                        </td>

                                        <td class="py-3 px-6 text-center bg-muted/5">
                                            <span class="text-xs font-bold px-2 py-1 rounded-full border"
                                                  :class="item.total_reserved > 0 ? 'bg-orange-500/10 text-orange-600 border-orange-500/20' : 'bg-background text-muted-foreground'">
                                                {{ item.total_reserved }}
                                            </span>
                                        </td>

                                        <td class="py-3 px-6 text-right border-l border-border/50 bg-primary/5">
                                            <span class="text-lg font-black"
                                                  :class="{
                                                      'text-primary': item.status === 'IN_STOCK',
                                                      'text-amber-600': item.status === 'LOW_STOCK',
                                                      'text-red-600': item.status === 'OUT_OF_STOCK'
                                                  }">
                                                {{ item.available_quantity }}
                                            </span>
                                        </td>

                                    </tr>
                                </template>
                            </template>

                            <tr v-else>
                                <td colspan="6" class="py-24 text-center">
                                    <PackageOpen :size="48" class="mx-auto text-muted-foreground/30 mb-4" stroke-width="1.5"/>
                                    <p class="font-black text-muted-foreground uppercase tracking-widest text-sm">No se encontró stock</p>
                                    <p class="text-xs text-muted-foreground mt-1">Intenta con otros filtros o verifica las sucursales.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div v-if="stock.links && stock.data.length > 0" class="mt-6 flex justify-center">
                <p class="text-xs font-bold text-muted-foreground uppercase">Paginación ({{ stock.meta?.total || 0 }} registros)</p>
            </div>

        </div>
    </AdminLayout>
</template>