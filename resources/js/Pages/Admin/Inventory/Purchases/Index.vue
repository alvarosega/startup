<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Admin/Pagination.vue'; 
import { Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import axios from 'axios'; // LEY: Requerido para carga asíncrona
import { 
    ChevronDown, MapPin, Package, Factory, 
    ShieldAlert, Plus, ListFilter, CreditCard, User, Clock, Loader2
} from 'lucide-vue-next';

const props = defineProps({ 
    purchases: { type: Object, required: true }, 
    branches: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) } 
});

// ESTADO DE UI
const expandedId = ref(null);
const detailsLoading = ref(false);
const purchaseDetails = ref({}); // Caché de items para evitar re-peticiones

const branchFilter = ref(props.filters.branch_id || '');
const typeFilter = ref(props.filters.type || '');

/**
 * Lógica de expansión con carga diferida (Lazy Loading)
 * Objetivo: Reducir el payload inicial en 85%.
 */
const toggleRow = async (purchaseId) => {
    if (expandedId.value === purchaseId) {
        expandedId.value = null;
        return;
    }

    expandedId.value = purchaseId;

    if (purchaseDetails.value[purchaseId]) return;

    detailsLoading.value = true;
    try {
        // LEY: Ruta debe estar definida en web.php y Ziggy
        const response = await axios.get(route('admin.purchases.items', purchaseId));
        purchaseDetails.value[purchaseId] = response.data.data;
    } catch (e) {
        console.error("VIOLACIÓN DE AUDITORÍA: Error al recuperar items del lote.", e);
    } finally {
        detailsLoading.value = false;
    }
};

const applyFilters = () => {
    router.get(route('admin.purchases.index'), {
        branch_id: branchFilter.value,
        type: typeFilter.value
    }, { preserveState: true, replace: true });
};

watch([branchFilter, typeFilter], applyFilters);

const formatMoney = (val) => new Intl.NumberFormat('es-BO', { 
    style: 'currency', 
    currency: 'BOB',
    minimumFractionDigits: 2 
}).format(val);

const shouldShowBranchHeader = (index) => {
    const data = props.purchases.data;
    if (!data || !data[index]) return false;
    if (index === 0) return true;
    return data[index].branch_id !== data[index - 1].branch_id;
};
</script>

<template>
    <AdminLayout>
        <div class="max-w-7xl mx-auto pb-20 p-4">
            
            <div class="flex flex-col md:flex-row justify-between items-end mb-8 gap-4">
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-foreground">Historial de Ingresos</h1>
                    <p class="text-xs text-muted-foreground uppercase font-bold">Auditoría Transaccional de Compras</p>
                </div>

                <div class="flex flex-wrap gap-3 w-full md:w-auto">
                    <div class="flex items-center bg-card border rounded-xl px-3 h-11 shadow-sm">
                        <MapPin :size="16" class="text-muted-foreground mr-2"/>
                        <select v-model="branchFilter" class="bg-transparent border-none text-xs font-bold focus:ring-0">
                            <option value="">Todas las Sedes</option>
                            <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                        </select>
                    </div>

                    <div class="flex items-center bg-card border rounded-xl px-3 h-11 shadow-sm">
                        <ListFilter :size="16" class="text-muted-foreground mr-2"/>
                        <select v-model="typeFilter" class="bg-transparent border-none text-xs font-bold focus:ring-0">
                            <option value="">Todos los Tipos</option>
                            <option value="LOT">Ordinarios (CMP)</option>
                            <option value="RELOT">Emergencia (EMG)</option>
                        </select>
                    </div>

                    <Link :href="route('admin.purchases.create')" class="inline-flex items-center gap-2 bg-primary text-white h-11 px-6 rounded-xl font-bold text-xs shadow-lg shadow-primary/20 hover:scale-[1.02] transition-transform">
                        <Plus :size="18" /> <span class="hidden sm:inline uppercase">Nuevo Registro</span>
                    </Link>
                </div>
            </div>

            <div class="space-y-4">
                <template v-for="(p, index) in purchases.data" :key="p.id">
                    
                    <div v-if="shouldShowBranchHeader(index)" class="flex items-center gap-4 pt-4">
                        <div class="h-px flex-1 bg-border"></div>
                        <div class="flex items-center gap-2 px-4 py-1 rounded-full bg-muted border font-black text-[10px] uppercase tracking-widest text-muted-foreground">
                            <MapPin :size="12"/> {{ p.branch_name }}
                        </div>
                        <div class="h-px flex-1 bg-border"></div>
                    </div>

                    <div class="card bg-card border hover:border-primary/40 transition-all overflow-hidden rounded-2xl shadow-sm"
                         :class="{'ring-2 ring-primary/10 border-primary/30': expandedId === p.id}">
                        
                        <div @click="toggleRow(p.id)" class="p-5 cursor-pointer flex flex-col md:flex-row justify-between items-center gap-4">
                            <div class="flex items-center gap-4 w-full md:w-auto">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center border shrink-0"
                                     :class="p.is_safety ? 'bg-orange-500/10 border-orange-500/20 text-orange-600' : 'bg-primary/10 border-primary/20 text-primary'">
                                    <ShieldAlert v-if="p.is_safety" :size="20"/>
                                    <Package v-else :size="20"/>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono font-black text-sm tracking-tighter">{{ p.document_number }}</span>
                                        <span v-if="p.is_safety" class="text-[8px] font-black bg-orange-500 text-white px-1.5 py-0.5 rounded">EMERGENCIA</span>
                                        <span class="text-[8px] font-black bg-muted text-muted-foreground px-1.5 py-0.5 rounded">{{ p.status }}</span>
                                    </div>
                                    <p class="text-xs font-bold text-foreground uppercase tracking-wide flex items-center gap-1 mt-1">
                                        <Factory :size="12" class="text-muted-foreground"/> {{ p.provider_name }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-6 w-full md:w-auto justify-between md:justify-end">
                                <div class="text-center md:text-right">
                                    <p class="text-[10px] font-black text-muted-foreground uppercase">Fecha</p>
                                    <p class="text-xs font-bold">{{ p.purchase_date }}</p>
                                </div>
                                <div class="text-center md:text-right">
                                    <p class="text-[10px] font-black text-muted-foreground uppercase">Monto Total</p>
                                    <p class="text-sm font-black text-primary">{{ formatMoney(p.total_amount) }}</p>
                                </div>
                                <ChevronDown :size="20" class="text-muted-foreground transition-transform" :class="{'rotate-180': expandedId === p.id}"/>
                            </div>
                        </div>

                        <div v-if="expandedId === p.id" class="border-t bg-muted/5 p-6 overflow-x-auto">
                            <div v-if="detailsLoading" class="flex flex-col items-center py-10 opacity-50">
                                <Loader2 class="animate-spin text-primary mb-2" :size="24"/>
                                <span class="text-[10px] font-black uppercase tracking-widest">Consultando MariaDB 11.8...</span>
                            </div>

                            <table v-else class="w-full text-left border-collapse min-w-[600px]">
                                <thead>
                                    <tr class="text-[10px] font-black text-muted-foreground uppercase tracking-widest border-b">
                                        <th class="pb-3 px-2">Lote</th>
                                        <th class="pb-3">SKU / Producto</th>
                                        <th class="pb-3 text-center">Cant.</th>
                                        <th class="pb-3 text-right">Costo Unit.</th>
                                        <th class="pb-3 text-right">Total Lote</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in purchaseDetails[p.id]" :key="item.id" class="border-b border-border/30 last:border-0 hover:bg-muted/10 transition-colors">
                                        <td class="py-3 px-2 font-mono text-[10px] font-bold text-muted-foreground">{{ item.lot_code }}</td>
                                        <td class="py-3">
                                            <p class="text-xs font-black">{{ item.sku_name }}</p>
                                            <p class="text-[9px] font-mono text-muted-foreground">EAN: {{ item.sku_code }}</p>
                                        </td>
                                        <td class="py-3 text-center">
                                            <span class="text-xs font-black">{{ item.current_quantity }}</span>
                                            <p v-if="item.reserved_quantity > 0" class="text-[8px] text-orange-500 font-bold">({{ item.reserved_quantity }} res.)</p>
                                        </td>
                                        <td class="py-3 text-right text-xs font-mono text-muted-foreground">{{ formatMoney(item.unit_cost) }}</td>
                                        <td class="py-3 text-right text-xs font-black">{{ formatMoney(item.current_quantity * item.unit_cost) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </template>
            </div>

            <div class="mt-8">
                <Pagination v-if="purchases.meta" :meta="purchases.meta" />
            </div>

            <div v-if="purchases.data.length === 0" class="py-20 text-center border-2 border-dashed rounded-3xl opacity-50">
                <Package :size="48" class="mx-auto text-muted-foreground mb-4"/>
                <p class="font-black text-muted-foreground uppercase tracking-widest">Sin transacciones registradas</p>
            </div>
        </div>
    </AdminLayout>
</template>