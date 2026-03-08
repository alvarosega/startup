<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { 
    ArrowLeft, History, MapPin, Search,
    ArrowDownRight, ArrowUpRight, MinusCircle
} from 'lucide-vue-next';

const props = defineProps({
    sku: Object,
    movements: Object,
    branches: Array,
    filters: Object
});

// Desempaquetado Seguro (DoD v2.0)
const movementList = computed(() => props.movements?.data || props.movements || []);

const branchFilter = ref(props.filters.branch_id || '');
const startDateFilter = ref(props.filters.start_date || '');
const endDateFilter = ref(props.filters.end_date || '');

const applyFilters = () => {
    router.get(route('admin.inventory.kardex', props.sku.id), {
        branch_id: branchFilter.value,
        start_date: startDateFilter.value,
        end_date: endDateFilter.value,
    }, { preserveState: true, replace: true });
};

watch([branchFilter, startDateFilter, endDateFilter], applyFilters);

const formatMoney = (val) => new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB' }).format(val);

const getMovementStyle = (type, quantity) => {
    if (quantity > 0) return { icon: ArrowDownRight, color: 'text-emerald-600', bg: 'bg-emerald-500/10' };
    if (quantity < 0) return { icon: ArrowUpRight, color: 'text-rose-600', bg: 'bg-rose-500/10' };
    return { icon: MinusCircle, color: 'text-muted-foreground', bg: 'bg-muted' };
};

const translateType = (type) => {
    const types = {
        'ENTRY_PURCHASE': 'Ingreso de Compra',
        'OUT_SALE': 'Salida por Venta',
        'RESERVE': 'Reserva Física',
        'ADJUSTMENT': 'Ajuste de Inventario'
    };
    return types[type] || type;
};
</script>

<template>
    <AdminLayout>
        <div class="max-w-[1200px] mx-auto pb-20 px-4">
            
            <div class="flex items-center gap-4 pt-6 mb-8">
                <Link :href="route('admin.inventory.index')" class="btn btn-ghost btn-square shrink-0 text-muted-foreground">
                    <ArrowLeft :size="20"/>
                </Link>
                <div>
                    <h1 class="text-2xl font-black tracking-tight flex items-center gap-3">
                        <History class="text-primary" :size="26"/>
                        Kardex Logístico
                    </h1>
                    <p class="text-sm font-bold text-muted-foreground flex items-center gap-2 mt-1">
                        {{ sku.product }} <span class="text-xs text-muted-foreground/60">|</span> {{ sku.name }}
                        <span class="text-[10px] bg-muted px-2 py-0.5 rounded-full font-mono uppercase border">EAN: {{ sku.code }}</span>
                    </p>
                </div>
            </div>

            <div class="card p-4 mb-6 flex flex-wrap gap-4 items-end bg-card border shadow-sm">
                <div v-if="branches && branches.length > 0" class="flex-1 min-w-[200px]">
                    <label class="text-[10px] font-black uppercase text-muted-foreground mb-1 block">Sucursal</label>
                    <div class="flex items-center bg-muted/20 border rounded-xl px-3 h-10">
                        <MapPin :size="14" class="text-muted-foreground mr-2 shrink-0"/>
                        <select v-model="branchFilter" class="bg-transparent border-none text-xs font-bold w-full focus:ring-0 p-0">
                            <option value="">Consolidado Global</option>
                            <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                        </select>
                    </div>
                </div>
                <div class="flex-1 min-w-[150px]">
                    <label class="text-[10px] font-black uppercase text-muted-foreground mb-1 block">Desde</label>
                    <input v-model="startDateFilter" type="date" class="form-input w-full h-10 text-xs bg-muted/20" />
                </div>
                <div class="flex-1 min-w-[150px]">
                    <label class="text-[10px] font-black uppercase text-muted-foreground mb-1 block">Hasta</label>
                    <input v-model="endDateFilter" type="date" class="form-input w-full h-10 text-xs bg-muted/20" />
                </div>
            </div>

            <div class="card overflow-hidden border shadow-sm bg-card">
                <div class="overflow-x-auto">
                    <table class="w-full text-left whitespace-nowrap">
                        <thead class="bg-muted/30 border-b border-border text-[10px] font-black uppercase tracking-widest text-muted-foreground">
                            <tr>
                                <th class="px-6 py-4">Fecha / Sucursal</th>
                                <th class="px-6 py-4">Operación</th>
                                <th class="px-6 py-4">Referencia</th>
                                <th class="px-6 py-4 text-center">Cantidad</th>
                                <th class="px-6 py-4 text-center border-l border-border/50">Costo Unit.</th>
                                <th class="px-6 py-4 text-right bg-primary/5 text-primary border-l border-border/50">Saldo Físico</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/50">
                            <tr v-for="mov in movementList" :key="mov.id" class="hover:bg-muted/10 transition-colors">
                                
                                <td class="px-6 py-3">
                                    <p class="text-xs font-black text-foreground">{{ mov.created_at }}</p>
                                    <p class="text-[9px] font-bold text-muted-foreground mt-0.5 flex items-center gap-1">
                                        <MapPin :size="10"/> {{ mov.branch_name }}
                                    </p>
                                </td>

                                <td class="px-6 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded flex items-center justify-center border" :class="[getMovementStyle(mov.type, mov.quantity).bg, getMovementStyle(mov.type, mov.quantity).color]">
                                            <component :is="getMovementStyle(mov.type, mov.quantity).icon" :size="12" stroke-width="3"/>
                                        </div>
                                        <div>
                                            <p class="text-[11px] font-black uppercase text-foreground">{{ translateType(mov.type) }}</p>
                                            <p class="text-[9px] font-bold text-muted-foreground">LOTE: <span class="font-mono">{{ mov.lot_code }}</span></p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-3">
                                    <p class="text-[11px] font-bold text-foreground truncate max-w-[200px]" :title="mov.reference">{{ mov.reference || 'S/N' }}</p>
                                    <p class="text-[9px] text-muted-foreground mt-0.5 font-medium">{{ mov.admin_name }}</p>
                                </td>

                                <td class="px-6 py-3 text-center">
                                    <span class="text-sm font-black" :class="getMovementStyle(mov.type, mov.quantity).color">
                                        {{ mov.quantity > 0 ? '+' : '' }}{{ mov.quantity }}
                                    </span>
                                </td>

                                <td class="px-6 py-3 text-center border-l border-border/50 bg-muted/5">
                                    <span class="text-xs font-mono font-medium text-muted-foreground">{{ formatMoney(mov.unit_cost) }}</span>
                                </td>

                                <td class="px-6 py-3 text-right border-l border-border/50 bg-primary/5">
                                    <span class="text-sm font-black text-foreground">{{ mov.running_balance }}</span>
                                </td>
                            </tr>

                            <tr v-if="movementList.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center text-muted-foreground">
                                    <Search :size="32" class="mx-auto mb-3 opacity-30"/>
                                    <p class="text-xs font-black uppercase tracking-widest">Sin movimientos registrados</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="mt-6 flex justify-center" v-if="movements?.meta">
                 <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Paginación ({{ movements.meta.total }} Movimientos)</p>
            </div>

        </div>
    </AdminLayout>
</template>