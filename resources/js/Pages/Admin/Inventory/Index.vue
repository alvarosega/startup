<script setup>
import { ref, watch, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
    Search, SlidersHorizontal, Warehouse, ShieldAlert, 
    ShieldCheck, CornerDownRight, ShieldX, RefreshCw
} from 'lucide-vue-next';
import debounce from 'lodash/debounce';
import axios from 'axios';

const props = defineProps({
    balances: Array,
    branches: Array,
    filters: Object
});

const search = ref(props.filters?.search || '');
const selectedBranch = ref(props.filters?.branch_id || '');
const activeAlertFilter = ref('all'); // 'all', 'quarantine', 'stockout'
const processingAction = ref(false);

// Control local de lotes para el panel expandido
const expandedSkuId = ref(null);
const skuLots = ref([]);
const loadingLots = ref(false);

// Formulario de Ajuste Táctico
const adjustmentForm = ref({
    inventory_lot_id: '',
    quantity: 0,
    reason: ''
});

const handleFilter = debounce(() => {
    router.get(route('admin.inventory.index'), {
        search: search.value,
        branch_id: selectedBranch.value
    }, { preserveState: true, replace: true, preserveScroll: true });
}, 300);

watch([search, selectedBranch], () => handleFilter());

const fetchLots = async (skuId, branchId) => {
    if (expandedSkuId.value === skuId) {
        expandedSkuId.value = null;
        return;
    }
    expandedSkuId.value = skuId;
    loadingLots.value = true;
    skuLots.value = [];
    
    try {
        const response = await axios.get(route('admin.inventory.lots', skuId), {
            params: { branch_id: branchId }
        });
        skuLots.value = response.data.lots || [];
    } catch (e) {
        alert('FALLO_SISTEMA_GESTOR: No se pudo compilar el mapa de lotes líquidos.');
    } finally {
        loadingLots.value = false;
    }
};

const executeAdjustment = async (endpointRoute, lotId) => {
    if (adjustmentForm.value.quantity <= 0 || !adjustmentForm.value.reason.trim()) {
        alert('ERROR_VALIDACIÓN: Cantidad y justificación obligatorias.');
        return;
    }
    processingAction.value = true;
    try {
        await axios.post(route(endpointRoute), {
            inventory_lot_id: lotId,
            quantity: adjustmentForm.value.quantity,
            reason: adjustmentForm.value.reason
        });
        
        // Recarga atómica del estado
        expandedSkuId.value = null;
        adjustmentForm.value = { inventory_lot_id: '', quantity: 0, reason: '' };
        router.reload({ preserveScroll: true });
    } catch (error) {
        alert(error.response?.data?.message || 'VIOLACIÓN_TRANSACCIONAL: Operación denegada.');
    } finally {
        processingAction.value = false;
    }
};

const processedBalances = computed(() => {
    if (activeAlertFilter.value === 'stockout') {
        return props.balances.filter(b => b.total_available <= 0);
    }
    if (activeAlertFilter.value === 'quarantine') {
        return props.balances.filter(b => b.total_quarantine > 0);
    }
    return props.balances;
});
</script>

<template>
    <Head title="Inventario - Saldos de Almacén" />
    <AdminLayout>
        <template #header>
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 select-none">
                <div>
                    <h1 class="text-xl md:text-2xl font-black tracking-tight text-neutral-900 dark:text-neutral-50 uppercase italic">Saldos de Almacén</h1>
                    <p class="text-[10px] text-neutral-500 dark:text-neutral-400 font-mono tracking-wider uppercase mt-0.5">Consola analítica de disponibilidad y existencias físicas</p>
                </div>
            </div>
        </template>

        <div class="space-y-4">
            <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-md p-3 grid grid-cols-1 lg:grid-cols-12 gap-3 shadow-sm select-none">
                <div class="lg:col-span-6 relative">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400" :size="16" />
                    <input v-model="search" type="text" placeholder="Buscar SKU, Código de Barra o Designación Maestra..." class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded-md pl-9 pr-4 py-1.5 text-xs font-mono uppercase text-neutral-900 dark:text-neutral-50 outline-none focus:border-neutral-400 dark:focus:border-neutral-700" />
                </div>
                <div class="lg:col-span-3 relative">
                    <Warehouse class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400" :size="16" />
                    <select v-model="selectedBranch" class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded-md pl-9 pr-4 py-1.5 text-xs font-bold uppercase text-neutral-900 dark:text-neutral-50 outline-none">
                        <option value="">Todos los Nodos Logísticos</option>
                        <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name.toUpperCase() }}</option>
                    </select>
                </div>
                <div class="lg:col-span-3 grid grid-cols-3 border border-neutral-200 dark:border-neutral-800 rounded-md overflow-hidden text-[10px] font-mono font-black text-center uppercase">
                    <button @click="activeAlertFilter = 'all'" :class="activeAlertFilter === 'all' ? 'bg-neutral-900 text-white dark:bg-neutral-50 dark:text-neutral-950' : 'bg-neutral-50 dark:bg-neutral-950 text-neutral-400'" class="py-1.5">TODOS</button>
                    <button @click="activeAlertFilter = 'stockout'" :class="activeAlertFilter === 'stockout' ? 'bg-rose-600 text-white' : 'bg-neutral-50 dark:bg-neutral-950 text-rose-600/60'" class="py-1.5 border-l border-r border-neutral-200 dark:border-neutral-800">QUIEBRE</button>
                    <button @click="activeAlertFilter = 'quarantine'" :class="activeAlertFilter === 'quarantine' ? 'bg-amber-500 text-white' : 'bg-neutral-50 dark:bg-neutral-950 text-amber-500/60'" class="py-1.5">RETENIDO</button>
                </div>
            </div>

            <div class="border border-neutral-200 dark:border-neutral-800 rounded-md bg-white dark:bg-neutral-900 shadow-sm overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-neutral-50/70 dark:bg-neutral-900/50 border-b border-neutral-200 dark:border-neutral-800 text-[10px] font-mono font-bold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 select-none">
                            <th class="p-3 w-12 text-center">LOTE</th>
                            <th class="p-3 min-w-[260px]">ARTÍCULO (SKU MAESTRO)</th>
                            <th class="p-3 text-right font-mono w-28">FÍSICO_REAL</th>
                            <th class="p-3 text-right font-mono w-28">RESERVADO</th>
                            <th class="p-3 text-right font-mono w-28">SEGURIDAD</th>
                            <th class="p-3 text-right font-mono w-28">CUARENTENA</th>
                            <th class="p-3 text-right font-mono w-32 bg-neutral-50 dark:bg-neutral-950/40">STOCK_DISPONIBLE</th>
                            <th class="p-3 w-16 text-center">HIST</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800">
                        <template v-for="balance in processedBalances" :key="`${balance.branch_id}-${balance.sku_id}`">
                            <tr class="hover:bg-neutral-50/40 dark:hover:bg-neutral-800/20 transition-colors">
                                <td class="p-3 text-center select-none">
                                    <button @click="fetchLots(balance.sku_id, balance.branch_id)" class="p-1 rounded border border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-950 text-neutral-400 hover:text-neutral-900 dark:hover:text-white transition-all flex items-center justify-center mx-auto">
                                        <SlidersHorizontal :size="14" :class="{'rotate-90 text-neutral-900 dark:text-white': expandedSkuId === balance.sku_id}" class="transition-transform" />
                                    </button>
                                </td>
                                <td class="p-3">
                                    <div class="font-bold text-neutral-900 dark:text-neutral-50 uppercase select-all tracking-tight leading-tight">{{ balance.sku_name }}</div>
                                    <div class="text-[10px] font-mono text-neutral-400 dark:text-neutral-500 uppercase mt-0.5 select-all">{{ balance.sku_code }}</div>
                                </td>
                                <td class="p-3 text-right font-mono font-medium text-neutral-400 dark:text-neutral-500 select-all">{{ Number(balance.total_physical).toFixed(3) }}</td>
                                <td class="p-3 text-right font-mono text-neutral-400 dark:text-neutral-500 select-all">{{ Number(balance.total_reserved).toFixed(3) }}</td>
                                <td class="p-3 text-right font-mono text-neutral-400 dark:text-neutral-500 select-all">{{ Number(balance.total_safety).toFixed(3) }}</td>
                                <td class="p-3 text-right font-mono text-neutral-400 dark:text-neutral-500 select-all">{{ Number(balance.total_quarantine).toFixed(3) }}</td>
                                <td class="p-3 text-right font-mono font-black bg-neutral-50/50 dark:bg-neutral-950/20 select-all text-xs" :class="balance.total_available <= 0 ? 'text-rose-600 dark:text-rose-400 bg-rose-50/20 dark:bg-rose-950/10' : 'text-neutral-900 dark:text-white'">
                                    {{ Number(balance.total_available).toFixed(3) }}
                                </td>
                                <td class="p-3 text-center select-none">
                                    <button @click="router.get(route('admin.inventory.kardex', balance.sku_id), { branch_id: balance.branch_id })" class="text-neutral-400 hover:text-neutral-900 dark:hover:text-white p-1 rounded hover:bg-neutral-100 dark:hover:bg-neutral-800 flex items-center justify-center mx-auto border border-transparent hover:border-neutral-200 dark:hover:border-neutral-700 transition-all">
                                        <RefreshCw :size="14" />
                                    </button>
                                </td>
                            </tr>

                            <tr v-if="expandedSkuId === balance.sku_id" class="bg-neutral-50/30 dark:bg-neutral-950/20">
                                <td colspan="8" class="p-4 border-l-2 border-neutral-900 dark:border-white">
                                    <div v-if="loadingLots" class="p-8 text-center text-neutral-400 font-mono text-[11px] uppercase tracking-wider flex items-center justify-center gap-1.5">
                                        <span class="w-4 h-4 border-2 border-neutral-400 border-t-transparent animate-spin rounded-full"></span>
                                        <span>Extrayendo Datos Geodésicos de Lotes...</span>
                                    </div>
                                    <div v-else class="space-y-3">
                                        <h4 class="text-[10px] font-mono font-black text-neutral-400 dark:text-neutral-500 uppercase tracking-widest pl-1">Desglose de Lotes Disponibles (FEFO)</h4>
                                        <div class="grid grid-cols-1 gap-2">
                                            <div v-for="lot in skuLots" :key="lot.id" class="border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 rounded p-3 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 shadow-xs">
                                                <div class="font-mono text-xs space-y-1">
                                                    <div>LOTE: <span class="font-bold text-neutral-900 dark:text-white select-all">{{ lot.lot_code }}</span></div>
                                                    <div class="text-[10px] text-neutral-400 uppercase">Vencimiento: <span class="font-bold">{{ lot.expiration_date || 'ETERNO' }}</span></div>
                                                    <div class="text-[10px] text-neutral-400 uppercase">Ordinario Líquido: <span class="text-neutral-900 dark:text-white font-bold">{{ lot.available_liquid.toFixed(3) }}</span> / Reservado: {{ lot.reserved_quantity.toFixed(3) }} / Seguridad: {{ lot.safety_quantity.toFixed(3) }}</div>
                                                </div>

                                                <div class="flex items-center gap-2 w-full md:w-auto font-mono">
                                                    <input v-model.number="adjustmentForm.quantity" type="number" step="1" min="0.001" placeholder="Cant." class="w-20 bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 px-2 py-1 text-xs text-right text-neutral-900 dark:text-neutral-50 rounded" />
                                                    <input v-model="adjustmentForm.reason" type="text" placeholder="Razón del ajuste..." class="w-44 bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 px-2 py-1 text-xs text-neutral-900 dark:text-neutral-50 rounded placeholder:text-neutral-400/40" />
                                                    <button @click="executeAdjustment('admin.inventory.transfer-safety', lot.id)" :disabled="processingAction" class="bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 text-neutral-900 dark:text-neutral-50 border border-neutral-300 dark:border-neutral-700 px-3 py-1 text-[10px] font-bold uppercase rounded flex items-center gap-1">
                                                        <ShieldCheck :size="12" /> +SAFETY
                                                    </button>
                                                    <button @click="executeAdjustment('admin.inventory.isolate-quarantine', lot.id)" :disabled="processingAction" class="bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/30 dark:hover:bg-rose-950/50 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-900/60 px-3 py-1 text-[10px] font-bold uppercase rounded flex items-center gap-1">
                                                        <ShieldX :size="12" /> CUARENTENA
                    </button>
                                                </div>
                                            </div>
                                            <div v-if="skuLots.length === 0" class="p-4 border border-dashed border-neutral-200 dark:border-neutral-800 text-center text-neutral-400 italic text-[11px] uppercase font-mono rounded bg-neutral-50/50 dark:bg-neutral-950/20">
                                                No existen lotes ordinarios remanentes en este almacén.
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <tr v-if="processedBalances.length === 0">
                            <td colspan="8" class="p-16 text-center text-neutral-400 font-mono select-none">
                                <ShieldAlert class="mx-auto text-neutral-300 dark:text-neutral-700 mb-2" :size="32" />
                                <span class="text-xs uppercase font-bold tracking-wider">Ninguna celda matricial de inventario intersecta los filtros</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>