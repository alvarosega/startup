<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { 
    ChevronDown, MapPin, Package, Factory, 
    ShieldAlert, Plus, ListFilter, CreditCard, User, Clock
} from 'lucide-vue-next';

const props = defineProps({ 
    purchases: Object, 
    branches: Array,
    filters: Object 
});

const expandedRows = ref([]);
const branchFilter = ref(props.filters.branch_id || '');
const typeFilter = ref(props.filters.type || '');

const shouldShowBranchHeader = (index) => {
    const data = props.purchases?.data;
    if (!data || !data[index]) return false;
    if (index === 0) return true;
    return data[index]?.branch_id !== data[index - 1]?.branch_id;
};

const applyFilters = () => {
    router.get(route('admin.purchases.index'), {
        branch_id: branchFilter.value,
        type: typeFilter.value
    }, { preserveState: true, replace: true });
};

watch([branchFilter, typeFilter], applyFilters);

const formatMoney = (val) => new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB' }).format(val);
</script>

<template>
    <AdminLayout>
        <div class="max-w-7xl mx-auto pb-20">
            
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
                            <option value="LOT">Ordinarios (LOT)</option>
                            <option value="RELOT">Emergencia (RELOT)</option>
                        </select>
                    </div>

                    <Link :href="route('admin.purchases.create')" class="btn btn-primary h-11 px-6 shadow-lg shadow-primary/20">
                        <Plus :size="18" /> <span class="hidden sm:inline">Nuevo Registro</span>
                    </Link>
                </div>
            </div>

            <div class="space-y-8">
                <template v-for="(p, index) in purchases.data" :key="p.id">
                    
                    <div v-if="shouldShowBranchHeader(index)" class="flex items-center gap-4 pt-4">
                        <div class="h-px flex-1 bg-border"></div>
                        <div class="flex items-center gap-2 px-4 py-1 rounded-full bg-muted border font-black text-[10px] uppercase tracking-widest text-muted-foreground">
                            <MapPin :size="12"/> {{ p.branch_name }}
                        </div>
                        <div class="h-px flex-1 bg-border"></div>
                    </div>

                    <div class="card group bg-card border hover:border-primary/40 transition-all overflow-hidden">
                        <div @click="expandedRows.includes(p.id) ? expandedRows = expandedRows.filter(id => id !== p.id) : expandedRows.push(p.id)" 
                             class="p-5 cursor-pointer flex flex-col md:flex-row justify-between items-center gap-4">
                            
                            <div class="flex items-center gap-4 w-full md:w-auto">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center border transition-colors shrink-0"
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
                                    <p class="text-[9px] font-bold text-muted-foreground flex items-center gap-1 mt-0.5">
                                        <User :size="10"/> {{ p.admin_name }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-6 w-full md:w-auto justify-between md:justify-end">
                                <div v-if="p.payment_type === 'CREDIT'" class="text-center md:text-right bg-red-500/10 border border-red-500/20 px-2 py-1 rounded-lg">
                                    <p class="text-[9px] font-black text-red-600 uppercase flex items-center gap-1"><Clock :size="10"/> Vence</p>
                                    <p class="text-xs font-bold text-red-700">{{ p.payment_due_date || 'No definida' }}</p>
                                </div>
                                <div v-else class="text-center md:text-right bg-emerald-500/10 border border-emerald-500/20 px-2 py-1 rounded-lg">
                                    <p class="text-[9px] font-black text-emerald-600 uppercase flex items-center gap-1"><CreditCard :size="10"/> Contado</p>
                                    <p class="text-xs font-bold text-emerald-700">Pagado</p>
                                </div>

                                <div class="text-center md:text-right">
                                    <p class="text-[10px] font-black text-muted-foreground uppercase">Fecha Ingreso</p>
                                    <p class="text-xs font-bold">{{ p.purchase_date }}</p>
                                </div>
                                <div class="text-center md:text-right">
                                    <p class="text-[10px] font-black text-muted-foreground uppercase">Monto Transacción</p>
                                    <p class="text-sm font-black text-foreground">{{ formatMoney(p.total_amount) }}</p>
                                </div>
                                <ChevronDown :size="20" class="text-muted-foreground transition-transform" :class="{'rotate-180': expandedRows.includes(p.id)}"/>
                            </div>
                        </div>

                        <div v-if="expandedRows.includes(p.id)" class="border-t bg-muted/5 p-6 animate-in slide-in-from-top-2">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="text-[10px] font-black text-muted-foreground uppercase tracking-widest border-b border-border/50">
                                        <th class="pb-3 px-2">Código Lote</th>
                                        <th class="pb-3">Producto / SKU</th>
                                        <th class="pb-3 text-center">Caducidad</th>
                                        <th class="pb-3 text-center">Cant. Original</th>
                                        <th class="pb-3 text-center">Disponible</th>
                                        <th class="pb-3 text-right">Costo Unit.</th>
                                        <th class="pb-3 text-right">Valor Lote</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in p.details" :key="item.id" class="border-b border-border/30 last:border-0 hover:bg-muted/10 transition-colors">
                                        <td class="py-3 px-2 font-mono text-[10px] font-bold text-muted-foreground">{{ item.lot_code }}</td>
                                        <td class="py-3">
                                            <p class="text-xs font-black text-foreground">{{ item.sku_name }}</p>
                                            <p class="text-[9px] font-mono text-muted-foreground">EAN: {{ item.sku_code }}</p>
                                        </td>
                                        <td class="py-3 text-center text-xs font-bold text-muted-foreground">
                                            {{ item.expiration_date || 'N/A' }}
                                        </td>
                                        <td class="py-3 text-center">
                                            <span class="text-[10px] font-bold text-muted-foreground">{{ item.initial_quantity }}</span>
                                        </td>
                                        <td class="py-3 text-center">
                                            <span class="text-xs font-black px-2 py-0.5 rounded border" 
                                                  :class="item.current_quantity === 0 ? 'bg-red-500/10 border-red-500/20 text-red-600' : 'bg-background'">
                                                {{ item.current_quantity }}
                                            </span>
                                            <p v-if="item.reserved_quantity > 0" class="text-[8px] text-orange-500 font-bold mt-0.5">{{ item.reserved_quantity }} Reservados</p>
                                        </td>
                                        <td class="py-3 text-right text-xs font-mono text-muted-foreground">{{ formatMoney(item.unit_cost) }}</td>
                                        <td class="py-3 text-right text-xs font-black text-foreground">{{ formatMoney(item.initial_quantity * item.unit_cost) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                            <div v-if="p.notes" class="mt-4 p-3 rounded-lg bg-background border border-dashed text-[10px] text-muted-foreground italic">
                                Observaciones: "{{ p.notes }}"
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <div v-if="purchases.data.length === 0" class="py-20 text-center border-2 border-dashed rounded-3xl opacity-50">
                <Package :size="48" class="mx-auto text-muted-foreground mb-4"/>
                <p class="font-black text-muted-foreground uppercase tracking-widest">Sin transacciones registradas</p>
            </div>
        </div>
    </AdminLayout>
</template>