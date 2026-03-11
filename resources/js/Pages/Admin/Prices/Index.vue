<script setup>
import { ref, watch, computed } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
    Search, X, Save, Edit2, Check, Clock, User, AlertTriangle
} from 'lucide-vue-next';
import debounce from 'lodash/debounce';

const props = defineProps({
    products: Object,
    branches: Array,
    filters: Object
});

// --- DESEMPAQUETADO SEGURO (DoD v2.0) ---
const productsList = computed(() => props.products?.data || []);

// --- ESTADO ---
const search = ref(props.filters?.search || '');
const isDrawerOpen = ref(false);
const activeSku = ref(null);
const activeBranchId = ref(null);
const editingType = ref(null);

// --- FORMULARIO BLINDADO ---
// ELIMINA 'priority' y asegura 'list_price'
const form = useForm({
    sku_id: '', 
    branch_id: '', 
    type: '', 
    list_price: 0, // <--- REQUERIDO PARA AUDITORÍA
    final_price: 0,
    min_quantity: 1, 
    valid_from: '', 
    valid_to: null,
});

const priceTypes = ['regular', 'offer', 'member', 'wholesale', 'liquidation', 'staff'];

// --- UTILIDADES DE ALTO RENDIMIENTO (CORREGIDAS) ---

// 1. Desempaqueta sin piedad el array, ya sea que venga crudo, envuelto en proxy o en 'data'
const getPricesForBranch = (sku, branchId) => {
    let rawMatrix = sku?.prices_matrix?.[branchId];
    if (!rawMatrix) return [];
    
    // Si Laravel Resource lo envolvió en un objeto con { data: [...] }
    if (typeof rawMatrix === 'object' && !Array.isArray(rawMatrix) && rawMatrix.data) {
        rawMatrix = rawMatrix.data;
    }
    
    return Array.isArray(rawMatrix) ? rawMatrix : [];
};

// 2. Extrae un precio seguro y garantiza que devuelva null si no lo encuentra, no undefined.
const getPriceByType = (type) => {
    if (!activeSku.value || !activeBranchId.value) return null;
    const prices = getPricesForBranch(activeSku.value, activeBranchId.value);
    return prices.find(p => p.type === type) || null;
};

// 3. Función auxiliar para el template
const getRegularPrice = (sku, branchId) => {
    const prices = getPricesForBranch(sku, branchId);
    return prices.find(p => p.type === 'regular') || null;
};

// --- MOTOR DE BÚSQUEDA ---
watch(search, debounce((value) => {
    router.get(route('admin.prices.index'), { search: value || undefined }, { 
        preserveState: true, 
        replace: true 
    });
}, 400));

// --- ACCIONES QUIRÚRGICAS ---
const openManager = (sku, branchId) => {
    activeSku.value = sku;
    activeBranchId.value = branchId;
    editingType.value = null;
    isDrawerOpen.value = true;
};
const startEdit = (type) => {
    editingType.value = type;
    const existing = getPriceByType(type);
    const regular = getPriceByType('regular');
    
    // El precio base del SKU viene directo del nivel superior
    const defaultPrice = regular?.final_price ?? activeSku.value?.base_price ?? 0;
    
    form.sku_id = activeSku.value.id;
    form.branch_id = activeBranchId.value;
    form.type = type;
    
    form.list_price = existing?.list_price ?? defaultPrice;
    form.final_price = existing?.final_price ?? defaultPrice;
    form.min_quantity = existing?.min_quantity ?? (type === 'wholesale' ? 6 : 1);
    form.valid_from = existing?.valid_from?.split('T')[0] || new Date().toISOString().slice(0, 10);
    form.valid_to = existing?.valid_to?.split('T')[0] || null;
};

const saveInline = () => {
    form.post(route('admin.prices.store'), {
        preserveScroll: true,
        onSuccess: () => {
            editingType.value = null;
        }
    });
};
watch(() => props.products, () => {
    // Si el modal está abierto cuando llegan datos frescos del servidor
    if (isDrawerOpen.value && activeSku.value) {
        // Encontrar el producto actualizado en los nuevos props
        const updatedProduct = productsList.value.find(p => 
            p.skus.some(s => s.id === activeSku.value.id)
        );
        
        // Si el producto existe, buscar el SKU exacto y actualizar la referencia activa
        if (updatedProduct) {
            const updatedSku = updatedProduct.skus.find(s => s.id === activeSku.value.id);
            if (updatedSku) {
                activeSku.value = updatedSku;
            }
        }
    }
}, { deep: true });
</script>

<template>
    <AdminLayout>
        <Head title="Motor de Precios" />

        <div class="max-w-[1600px] mx-auto space-y-6 pb-10 px-4 mt-6">
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 border-b border-primary/30 pb-6">
                <div>
                    <h1 class="text-3xl font-black text-primary uppercase tracking-tighter">Matriz de Precios</h1>
                    <p class="text-[10px] font-mono text-muted-foreground mt-1 tracking-widest uppercase">
                        RESOLUCIÓN ESPACIAL POR SUCURSAL
                    </p>
                </div>
                <div class="relative w-full md:w-96 group/search">
                    <Search class="absolute left-4 top-1/2 -translate-y-1/2 text-primary/40 group-focus-within/search:text-primary transition-colors" :size="16" />
                    <input v-model="search" type="text" placeholder="[ BUSCAR SKU O EAN ]" 
                           class="w-full pl-12 pr-4 py-3 bg-background border border-primary/20 font-mono text-xs focus:border-primary outline-none transition-all uppercase text-foreground">
                </div>
            </div>

            <div class="bg-background/50 border border-primary/20 backdrop-blur-sm overflow-hidden relative">
                <div class="absolute top-0 left-0 w-1 h-full bg-primary"></div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-primary/5">
                            <tr class="text-[10px] font-mono font-black uppercase tracking-widest text-primary border-b border-primary/20">
                                <th class="px-8 py-5 border-r border-primary/10 min-w-[300px]">PRODUCTO_SKU</th>
                                <th v-for="branch in branches" :key="branch.id" class="px-6 py-5 text-center">{{ branch.name }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-primary/10">
                            <tr v-if="productsList.length === 0">
                                <td :colspan="branches.length + 1" class="py-20 text-center text-xs font-mono text-primary/40 uppercase tracking-[0.5em] animate-pulse">
                                    [ MATRIZ_VACÍA ]
                                </td>
                            </tr>
                            <template v-for="product in productsList" :key="product.id">
                                <tr v-for="sku in product.skus" :key="sku.id" 
                                    class="hover:bg-primary/5 transition-all cursor-pointer group/row" 
                                    @click="openManager(sku, activeBranchId || branches[0].id)">
                                    
                                    <td class="px-8 py-4 border-r border-primary/10">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-sm uppercase group-hover/row:text-primary transition-colors">{{ sku.name }}</span>
                                            <span class="text-[9px] font-mono text-muted-foreground tracking-widest mt-1">EAN: {{ sku.code || 'N/A' }}</span>
                                        </div>
                                    </td>
                                    
                                    <td v-for="branch in branches" :key="branch.id" class="px-4 py-4 text-center">
                                        <div class="flex flex-col items-center justify-center gap-1">
                                            <span class="font-mono font-black text-sm text-cyan-500">
                                                {{ getRegularPrice(sku, branch.id) ? '$' + getRegularPrice(sku, branch.id).final_price : '---' }}
                                            </span>
                                            <span v-if="!getRegularPrice(sku, branch.id)" class="text-[8px] text-destructive flex items-center gap-1 animate-pulse">
                                                <AlertTriangle :size="10"/> SIN_PRECIO
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <Transition enter-active-class="transition duration-300 ease-out" enter-from-class="translate-x-full" enter-to-class="translate-x-0" leave-active-class="transition duration-200 ease-in" leave-from-class="translate-x-0" leave-to-class="translate-x-full">
            <div v-if="isDrawerOpen" class="fixed inset-y-0 right-0 z-[60] flex w-full max-w-5xl shadow-2xl border-l border-primary/30">
                
                <div class="fixed inset-0 bg-background/80 backdrop-blur-sm -z-10" @click="isDrawerOpen = false"></div>
                
                <div class="w-full h-full bg-background flex flex-col relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-1 h-full bg-primary"></div>
                    
                    <div class="p-8 border-b border-primary/20 flex justify-between items-center bg-primary/5">
                        <div>
                            <h2 class="text-2xl font-black uppercase tracking-tighter text-primary">Gestor Quirúrgico</h2>
                            <p class="text-[10px] font-mono font-bold text-muted-foreground uppercase mt-1">
                                NODO: {{ activeSku?.name }} // SUCURSAL: {{ branches.find(b => b.id === activeBranchId)?.name }}
                            </p>
                        </div>
                        <button @click="isDrawerOpen = false" class="p-2 text-muted-foreground hover:text-destructive hover:bg-destructive/10 transition-colors border border-transparent hover:border-destructive/30">
                            <X :size="20"/>
                        </button>
                    </div>

                    <div class="px-8 py-4 bg-background border-b border-primary/10 flex gap-2 overflow-x-auto custom-scrollbar">
                        <button v-for="branch in branches" :key="branch.id"
                                @click="activeBranchId = branch.id; editingType = null;"
                                class="px-4 py-2 text-[10px] font-mono font-black uppercase transition-all whitespace-nowrap border"
                                :class="activeBranchId === branch.id ? 'bg-primary text-background border-primary shadow-neon-primary' : 'bg-transparent text-muted-foreground border-primary/20 hover:border-primary/50 hover:text-foreground'">
                            {{ branch.name }}
                        </button>
                    </div>

                    <div class="flex-1 overflow-auto p-8 custom-scrollbar">
                        
                        <div v-if="Object.keys(form.errors).length > 0" class="mb-6 p-4 bg-destructive/10 border border-destructive/50 text-destructive text-xs font-mono">
                            <p v-for="(error, key) in form.errors" :key="key">> {{ error }}</p>
                        </div>

                        <table class="w-full text-[11px] border-collapse">
                            <thead class="text-primary/60 font-mono font-black uppercase tracking-widest border-b border-primary/20">
                                <tr>
                                    <th class="py-4 pr-4 text-left">ESTRATEGIA</th>
                                    <th class="py-4 px-4 text-center">LISTA ($)</th>
                                    <th class="py-4 px-4 text-center">FINAL ($)</th>
                                    <th class="py-4 px-4 text-center">MIN_QTY</th>
                                    <th class="py-4 px-4 text-center">VIGENCIA_HASTA</th>
                                    <th class="py-4 px-4 text-center">AUDITORÍA</th>
                                    <th class="py-4 pl-4 text-right">EXEC</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-primary/10">
                                <tr v-for="type in priceTypes" :key="type" class="group/price hover:bg-primary/5 transition-colors">
                                    <td class="py-4 pr-4 font-black uppercase text-foreground">
                                        <span class="border-l-2 border-primary pl-2">{{ type }}</span>
                                    </td>
                                    
                                    <template v-if="editingType === type">
                                        <td class="py-4 px-2 text-center">
                                            <input v-model="form.list_price" type="number" step="0.01" class="w-20 text-center font-mono font-black border border-primary/50 rounded-none bg-background text-muted-foreground focus:border-primary outline-none">
                                        </td>
                                        <td class="py-4 px-2 text-center">
                                            <input v-model="form.final_price" type="number" step="0.01" class="w-20 text-center font-mono font-black border border-cyan-500 rounded-none bg-cyan-500/10 text-cyan-400 focus:border-cyan-400 outline-none shadow-[0_0_10px_rgba(6,182,212,0.2)]">
                                        </td>
                                        <td class="py-4 px-2 text-center">
                                            <input v-model="form.min_quantity" type="number" class="w-16 text-center border border-primary/30 rounded-none bg-background text-foreground outline-none focus:border-primary">
                                        </td>
                                        <td class="py-4 px-2 text-center">
                                            <input v-model="form.valid_to" type="date" class="w-[110px] text-[10px] font-mono border border-primary/30 rounded-none bg-background text-foreground outline-none focus:border-primary">
                                        </td>
                                        <td class="py-4 px-2 text-center text-[9px] font-mono text-warning animate-pulse">
                                            [ OVERRIDE ]
                                        </td>
                                        <td class="py-4 pl-4 text-right">
                                            <button @click="saveInline" :disabled="form.processing" class="p-2 bg-primary text-background hover:bg-primary/80 transition-all border border-transparent disabled:opacity-50">
                                                <Check :size="16"/>
                                            </button>
                                        </td>
                                    </template>

                                    <template v-else>
                                        <td class="py-4 px-4 text-center font-mono text-sm text-muted-foreground line-through decoration-destructive/50">
                                            {{ getPriceByType(type) ? '$' + getPriceByType(type).list_price : '---' }}
                                        </td>
                                        <td class="py-4 px-4 text-center font-mono font-black text-sm text-cyan-500">
                                            {{ getPriceByType(type) ? '$' + getPriceByType(type).final_price : '---' }}
                                        </td>
                                        <td class="py-4 px-4 text-center font-bold text-foreground">
                                            {{ getPriceByType(type)?.min_quantity || '---' }}
                                        </td>
                                        <td class="py-4 px-4 text-center">
                                            <span v-if="getPriceByType(type)?.valid_to" class="flex items-center justify-center gap-1 text-warning font-mono text-[9px]">
                                                <Clock :size="10" /> {{ getPriceByType(type).valid_to.split('T')[0] }}
                                            </span>
                                            <span v-else class="text-[9px] font-mono text-primary/30">PERPETUO</span>
                                        </td>
                                        <td class="py-4 px-4 text-center">
                                            <span class="text-[9px] font-mono font-bold px-2 py-0.5 bg-primary/10 text-primary border border-primary/20">
                                                P{{ getPriceByType(type)?.priority || '0' }}
                                            </span>
                                        </td>

                                        <td class="py-4 px-4 text-center">
                                            <div v-if="getPriceByType(type)" class="flex flex-col items-center">
                                                <span class="text-[8px] uppercase font-black text-muted-foreground flex items-center gap-1">
                                                    <User :size="8"/> 
                                                    {{ getPriceByType(type).updater?.name || 'SISTEMA' }}
                                                </span>
                                            </div>
                                            <span v-else class="text-[8px] text-primary/20">---</span>
                                        </td>
                                        <td class="py-4 pl-4 text-right">
                                            <button @click="startEdit(type)" class="p-2 text-primary/50 hover:text-primary hover:bg-primary/10 border border-transparent hover:border-primary/30 transition-all">
                                                <Edit2 :size="16" />
                                            </button>
                                        </td>
                                    </template>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </Transition>
    </AdminLayout>
</template>

<style scoped>
.shadow-neon-primary { box-shadow: 0 0 15px hsl(var(--primary) / 0.3); }
.custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: hsl(var(--border) / 0.2); }
.custom-scrollbar::-webkit-scrollbar-thumb { background: hsl(var(--primary) / 0.5); border-radius: 0; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: hsl(var(--primary)); }
</style>