<script setup>
import { ref, computed, watch } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
    Search, Tag, X, Save, Store, DollarSign, Percent, 
    ChevronRight, Package, Barcode, AlertOctagon, 
    History, Zap, Plus, Trash2, Calendar, Users, ShoppingBag
} from 'lucide-vue-next';
import debounce from 'lodash/debounce';

const props = defineProps({
    products: Object,
    branches: Array,
    filters: Object
});

// --- ESTADO ---
const search = ref(props.filters.search || '');
const selectedBranchId = ref(props.filters.branch_id || '');
const isDrawerOpen = ref(false);
const activeSku = ref(null);
const activeBranchId = ref(null);

// --- FORMULARIO PARA NUEVA REGLA ---
const form = useForm({
    sku_id: '', branch_id: '', final_price: 0, list_price: 0,
    type: 'regular', min_quantity: 1, priority: 0,
    valid_from: new Date().toISOString().slice(0, 10),
    valid_to: null,
});

// --- LÓGICA DE REGLAS ACTIVAS ---
const activePrices = computed(() => {
    if (!activeSku.value || !activeBranchId.value) return [];
    return activeSku.value.prices_matrix?.[activeBranchId.value] || [];
});

// --- ACCIONES ---
const openPriceManager = (sku, branchId) => {
    activeSku.value = sku;
    activeBranchId.value = branchId;
    
    // Resetear form para nueva regla
    form.sku_id = sku.id;
    form.branch_id = branchId;
    form.final_price = sku.base_price;
    form.list_price = (sku.base_price * 1.15).toFixed(2);
    form.type = 'regular';
    
    isDrawerOpen.value = true;
};

const editExistingPrice = (price) => {
    form.final_price = price.final_price;
    form.list_price = price.list_price;
    form.type = price.type;
    form.priority = price.priority;
    form.min_quantity = price.min_quantity;
    form.valid_from = price.valid_from?.split('T')[0] || '';
    form.valid_to = price.valid_to?.split('T')[0] || null;
};

const submitNewRule = () => {
    form.post(route('admin.prices.store'), {
        preserveScroll: true,
        onSuccess: () => {
            // No cerramos el drawer para permitir añadir más reglas (Socio, Mayorista, etc)
            form.reset('final_price', 'list_price', 'type', 'priority', 'min_quantity');
        }
    });
};

// --- FILTROS ---
watch([search, selectedBranchId], debounce(() => {
    router.get(route('admin.prices.index'), { search: search.value, branch_id: selectedBranchId.value }, { preserveState: true });
}, 500));
</script>

<template>
    <AdminLayout>
        <Head title="Price Command Center" />

        <div class="max-w-[1600px] mx-auto space-y-6 pb-20">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 bg-card p-6 rounded-3xl border border-border shadow-sm">
                <div>
                    <h1 class="text-3xl font-black uppercase tracking-tighter">Price <span class="text-primary">Master</span></h1>
                    <p class="text-[10px] font-black uppercase text-muted-foreground tracking-widest mt-1">Gestión Multicapa por Sucursal</p>
                </div>
                <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
                    <input v-model="search" type="text" placeholder="Buscar..." class="form-input bg-muted/40 border-none rounded-2xl w-full sm:w-64">
                    <select v-model="selectedBranchId" class="form-input bg-primary text-primary-foreground font-black uppercase text-[10px] rounded-2xl border-none px-6">
                        <option value="">Todas las Sucursales</option>
                        <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                    </select>
                </div>
            </div>

            <div class="bg-card border border-border rounded-3xl shadow-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-muted/30 text-[10px] font-black uppercase tracking-widest text-muted-foreground border-b border-border">
                                <th class="px-6 py-5 sticky left-0 z-20 bg-card border-r border-border min-w-[300px]">Producto</th>
                                <th v-for="branch in (selectedBranchId ? branches.filter(b => b.id === selectedBranchId) : branches)" :key="branch.id" class="px-6 py-5 text-center min-w-[200px]">
                                    {{ branch.name }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/50">
                            <template v-for="product in products.data" :key="product.id">
                                <tr v-for="sku in product.skus" :key="sku.id" class="group hover:bg-muted/5">
                                    <td class="px-6 py-4 sticky left-0 z-10 bg-card group-hover:bg-muted/5 border-r border-border font-bold text-xs uppercase">
                                        {{ sku.name }}
                                        <div class="text-[9px] font-mono text-muted-foreground">{{ sku.code }}</div>
                                    </td>
                                    
                                    <td v-for="branch in (selectedBranchId ? branches.filter(b => b.id === selectedBranchId) : branches)" :key="branch.id" class="px-4 py-4 text-center">
                                        <div @click="openPriceManager(sku, branch.id)" class="p-3 rounded-2xl border border-dashed border-border hover:border-primary hover:bg-primary/5 cursor-pointer transition-all">
                                            <div v-if="sku.prices_matrix?.[branch.id]" class="space-y-1">
                                                <div class="text-sm font-black text-foreground">${{ sku.prices_matrix[branch.id][0].final_price }}</div>
                                                <div class="flex justify-center gap-1">
                                                    <div v-for="p in sku.prices_matrix[branch.id]" :key="p.id" 
                                                         class="w-2 h-2 rounded-full" 
                                                         :class="{
                                                            'bg-green-500': p.type === 'regular',
                                                            'bg-orange-500': p.type === 'offer',
                                                            'bg-blue-500': p.type === 'member',
                                                            'bg-purple-500': p.type === 'wholesale'
                                                         }" :title="p.type"></div>
                                                </div>
                                            </div>
                                            <div v-else class="text-[10px] text-muted-foreground/30 italic uppercase font-bold">Sin asignar</div>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <Transition name="slide">
            <div v-if="isDrawerOpen" class="fixed inset-0 z-[60] flex justify-end">
                <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="isDrawerOpen = false"></div>
                <div class="relative w-full max-w-lg bg-card h-full shadow-2xl flex flex-col border-l border-border">
                    
                    <div class="p-8 border-b border-border">
                        <div class="flex justify-between items-start">
                            <div>
                                <h2 class="text-2xl font-black uppercase tracking-tighter">Gestor de Precios</h2>
                                <p class="text-xs text-primary font-bold uppercase mt-1">Sucursal: {{ branches.find(b => b.id === activeBranchId)?.name }}</p>
                            </div>
                            <button @click="isDrawerOpen = false" class="p-2 rounded-full hover:bg-muted"><X /></button>
                        </div>
                    </div>

                    <div class="p-8 bg-muted/20 border-b border-border">
                        <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground block mb-4">Reglas de Precio Activas</label>
                        <div class="space-y-3">
                            <div v-if="activePrices.length === 0" class="text-xs font-bold text-muted-foreground italic p-4 border border-dashed border-border rounded-2xl text-center">
                                No hay reglas configuradas para esta sucursal.
                            </div>
                            <div v-for="price in activePrices" :key="price.id" 
                                 class="bg-card border border-border p-4 rounded-2xl flex justify-between items-center group hover:border-primary transition-all">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" 
                                         :class="{
                                            'bg-green-100 text-green-600': price.type === 'regular',
                                            'bg-orange-100 text-orange-600': price.type === 'offer',
                                            'bg-blue-100 text-blue-600': price.type === 'member',
                                            'bg-purple-100 text-purple-600': price.type === 'wholesale'
                                         }">
                                        <DollarSign v-if="price.type === 'regular'" :size="18"/>
                                        <Percent v-else-if="price.type === 'offer'" :size="18"/>
                                        <Users v-else-if="price.type === 'member'" :size="18"/>
                                        <ShoppingBag v-else :size="18"/>
                                    </div>
                                    <div>
                                        <p class="text-xs font-black uppercase">{{ price.type }}</p>
                                        <p class="text-lg font-mono font-black">${{ price.final_price }}</p>
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <button @click="editExistingPrice(price)" class="btn btn-ghost btn-xs text-primary opacity-0 group-hover:opacity-100 transition-opacity">Editar</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex-1 overflow-y-auto p-8 custom-scrollbar">
                        <div class="mb-6">
                            <h4 class="text-sm font-black uppercase tracking-tight mb-4 flex items-center gap-2">
                                <Plus class="text-primary" :size="16" /> Configurar Nueva Regla
                            </h4>
                            <div class="grid grid-cols-2 gap-4 bg-muted/30 p-2 rounded-2xl">
                                <button v-for="t in ['regular', 'offer', 'member', 'wholesale', 'liquidation', 'staff']" 
                                        :key="t" @click="form.type = t"
                                        class="py-2 text-[9px] font-black uppercase rounded-xl transition-all"
                                        :class="form.type === t ? 'bg-primary text-white shadow-lg' : 'text-muted-foreground hover:bg-muted'">
                                    {{ t }}
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-6 mb-8">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Precio Final</label>
                                <input v-model.number="form.final_price" type="number" step="0.01" class="form-input w-full h-12 text-lg font-black bg-muted/40 border-none rounded-2xl">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Cantidad Mínima</label>
                                <input v-model.number="form.min_quantity" type="number" class="form-input w-full h-12 font-bold bg-muted/40 border-none rounded-2xl">
                            </div>
                        </div>

                        <div class="space-y-4">
                            <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground flex items-center gap-2">
                                <Calendar :size="14" /> Periodo de Validez
                            </label>
                            <div class="flex items-center gap-4">
                                <input v-model="form.valid_from" type="date" class="form-input flex-1 h-11 bg-muted/40 border-none rounded-2xl text-xs">
                                <ArrowRight :size="16" class="text-muted-foreground" />
                                <input v-model="form.valid_to" type="date" class="form-input flex-1 h-11 bg-muted/40 border-none rounded-2xl text-xs">
                            </div>
                        </div>
                    </div>

                    <div class="p-8 border-t border-border bg-card">
                        <button @click="submitNewRule" :disabled="form.processing" 
                                class="w-full btn btn-primary py-7 rounded-2xl shadow-xl shadow-primary/20 uppercase font-black tracking-widest">
                            {{ form.processing ? 'Guardando...' : 'Guardar Regla de Precio' }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </AdminLayout>
</template>

<style scoped>
.slide-enter-active, .slide-leave-active { transition: transform 0.3s ease-in-out; }
.slide-enter-from, .slide-leave-to { transform: translateX(100%); }
</style>