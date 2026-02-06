<script setup>
import { ref, watch } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
    Search, Tag, X, Save, Globe, Store, 
    DollarSign, Percent, ChevronDown, ChevronRight, 
    MoreVertical, Package, Barcode
} from 'lucide-vue-next';
import debounce from 'lodash/debounce';

const props = defineProps({
    products: Object, // Paginado, contiene SKUs y prices_matrix
    branches: Array,
    filters: Object
});

// --- ESTADO ---
const search = ref(props.filters.search || '');
const expandedProducts = ref([]); // Controla qué productos están abiertos en móvil
const isModalOpen = ref(false);
const editingLabel = ref(''); 

// --- FORMULARIO ---
const form = useForm({
    sku_id: null,
    branch_id: null,
    final_price: '',
    list_price: '',
    type: 'regular',
    min_quantity: 1,
    valid_from: new Date().toISOString().slice(0, 10),
    valid_to: null,
});

// --- ACCIONES ---
watch(search, debounce((val) => {
    router.get(route('admin.prices.index'), { search: val }, { 
        preserveState: true, replace: true, preserveScroll: true 
    });
}, 500));

const toggleProduct = (id) => {
    if (expandedProducts.value.includes(id)) {
        expandedProducts.value = expandedProducts.value.filter(pid => pid !== id);
    } else {
        expandedProducts.value.push(id);
    }
};

// --- MODAL ---
const openModal = (sku, branchId, productName) => {
    const branchName = props.branches.find(b => b.id === branchId)?.name || 'Sucursal';
    editingLabel.value = `${branchName} • ${sku.name}`;
    
    const existingPrices = sku.prices_matrix ? sku.prices_matrix[branchId] : [];
    const activePrice = Array.isArray(existingPrices) && existingPrices.length > 0 
        ? existingPrices[existingPrices.length - 1] 
        : null;

    if (activePrice) {
        form.sku_id = sku.id;
        form.branch_id = branchId;
        form.final_price = activePrice.final_price;
        form.list_price = activePrice.list_price;
        form.type = activePrice.type;
        form.min_quantity = activePrice.min_quantity;
        form.valid_from = activePrice.valid_from ? activePrice.valid_from.substring(0, 10) : '';
        form.valid_to = activePrice.valid_to ? activePrice.valid_to.substring(0, 10) : '';
    } else {
        form.sku_id = sku.id;
        form.branch_id = branchId;
        form.final_price = sku.base_price || 0;
        form.list_price = sku.base_price ? (sku.base_price * 1.10).toFixed(2) : 0;
        form.type = 'regular';
        form.min_quantity = 1;
        form.valid_from = new Date().toISOString().slice(0, 10);
        form.valid_to = null;
    }
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
    form.clearErrors();
};

const submitPrice = () => {
    form.post(route('admin.prices.store'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
    });
};

// --- HELPERS ---
const getCellData = (sku, branchId) => {
    if (!sku.prices_matrix) return null;
    const prices = sku.prices_matrix[branchId];
    if (!prices || prices.length === 0) return null;
    const offer = prices.find(p => p.type === 'offer');
    return offer || prices[0];
};

const isOfferActive = (priceObj) => priceObj && priceObj.type === 'offer';

const getImageUrl = (imagePath) => {
    if (!imagePath) return null;
    if (imagePath.startsWith('http')) return imagePath;
    return `/storage/${imagePath}`;
};
</script>

<template>
    <AdminLayout>
        <Head title="Gestión de Precios" />

        <div class="pb-32 md:pb-10 h-full flex flex-col">
            
            <div class="mb-6 flex flex-col gap-4 px-4 md:px-0">
                <div>
                    <h1 class="text-2xl font-display font-black text-foreground tracking-tight">Matriz de Precios</h1>
                    <p class="text-xs text-muted-foreground">Configuración de precios por producto y sucursal.</p>
                </div>
                
                <div class="relative">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" :size="18" />
                    <input 
                        v-model="search" 
                        type="text" 
                        placeholder="Buscar producto..." 
                        class="w-full pl-10 pr-4 py-3 bg-background border border-border rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all outline-none text-sm shadow-sm"
                    >
                </div>
            </div>

            <div class="block lg:hidden space-y-4 px-4 md:px-0">
                <div v-for="product in products.data" :key="product.id" 
                     class="bg-card border border-border rounded-xl overflow-hidden shadow-sm transition-all"
                     :class="expandedProducts.includes(product.id) ? 'ring-1 ring-primary/30' : ''">
                    
                    <div class="p-3 flex items-center gap-3 cursor-pointer select-none bg-muted/10" 
                         @click="toggleProduct(product.id)">
                        
                        <div class="w-12 h-12 rounded-lg bg-background border border-border flex items-center justify-center shrink-0 overflow-hidden">
                            <img v-if="product.image_url" :src="getImageUrl(product.image_url)" class="w-full h-full object-cover">
                            <Package v-else :size="20" class="text-muted-foreground/40"/>
                        </div>

                        <div class="flex-1 min-w-0">
                            <h3 class="font-black text-sm text-foreground truncate">{{ product.name }}</h3>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="text-[10px] text-muted-foreground flex items-center gap-1">
                                    <Tag :size="10"/> {{ product.skus.length }} Variantes
                                </span>
                            </div>
                        </div>

                        <div class="w-8 h-8 flex items-center justify-center rounded-full bg-background border border-border/50">
                            <component :is="expandedProducts.includes(product.id) ? ChevronDown : ChevronRight" :size="16" class="text-muted-foreground"/>
                        </div>
                    </div>

                    <div v-show="expandedProducts.includes(product.id)" class="divide-y divide-border/40 border-t border-border/40">
                        <div v-for="sku in product.skus" :key="sku.id" class="p-3 bg-background">
                            
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex items-center gap-2">
                                    <Barcode :size="14" class="text-muted-foreground"/>
                                    <span class="text-xs font-bold text-foreground">{{ sku.name }}</span>
                                </div>
                                <span class="text-[10px] font-mono bg-muted px-1.5 rounded text-muted-foreground">{{ sku.code || 'S/C' }}</span>
                            </div>

                            <div class="grid grid-cols-1 gap-2">
                                <div v-for="branch in branches" :key="branch.id" 
                                     class="flex items-center justify-between p-2 rounded border border-border/60 bg-muted/5 active:bg-muted/20 active:scale-[0.99] transition-all cursor-pointer"
                                     @click="openModal(sku, branch.id, product.name)">
                                    
                                    <div class="flex items-center gap-2">
                                        <div class="w-1.5 h-1.5 rounded-full bg-primary/50"></div>
                                        <span class="text-[11px] font-medium text-muted-foreground">{{ branch.name }}</span>
                                    </div>

                                    <div class="text-right">
                                        <template v-if="getCellData(sku, branch.id)">
                                            <div v-if="isOfferActive(getCellData(sku, branch.id))" class="flex flex-col items-end leading-none">
                                                <span class="text-[9px] font-bold text-orange-600 bg-orange-100 px-1 rounded-[2px] mb-0.5">OFF</span>
                                                <span class="text-sm font-black text-orange-600">{{ getCellData(sku, branch.id).final_price }}</span>
                                            </div>
                                            <div v-else>
                                                <span class="text-sm font-bold text-foreground">{{ getCellData(sku, branch.id).final_price }}</span>
                                            </div>
                                        </template>
                                        <template v-else>
                                            <span class="text-[10px] text-primary underline decoration-dashed decoration-primary/30">Asignar</span>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="hidden lg:flex flex-1 flex-col bg-card border border-border rounded-xl shadow-sm overflow-hidden mx-4 md:mx-0">
                <div class="overflow-auto flex-1 relative custom-scrollbar">
                    <table class="w-full border-collapse">
                        <thead class="bg-muted/50 sticky top-0 z-30 shadow-sm text-xs uppercase font-bold text-muted-foreground">
                            <tr>
                                <th class="px-4 py-3 text-left sticky left-0 z-40 bg-muted border-b border-r border-border w-80 shadow-[1px_0_0_0_rgba(0,0,0,0.05)]">
                                    Producto / Variante
                                </th>
                                <th class="px-4 py-3 text-center sticky left-80 z-40 bg-muted border-b border-r border-border w-24">
                                    Base
                                </th>
                                <th v-for="branch in branches" :key="branch.id" 
                                    class="px-4 py-3 text-center min-w-[140px] border-b border-border bg-muted/50">
                                    {{ branch.name }}
                                </th>
                            </tr>
                        </thead>
                        
                        <tbody class="divide-y divide-border/50">
                            <template v-for="product in products.data" :key="product.id">
                                <tr class="bg-muted/20">
                                    <td :colspan="2 + branches.length" class="px-4 py-2 border-y border-border sticky left-0 z-20 bg-muted/20">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded bg-background border border-border overflow-hidden flex items-center justify-center">
                                                <img v-if="product.image_url" :src="getImageUrl(product.image_url)" class="w-full h-full object-cover">
                                                <Package v-else :size="16" class="text-muted-foreground"/>
                                            </div>
                                            <span class="font-black text-sm text-foreground tracking-tight">{{ product.name }}</span>
                                            <span class="text-[10px] text-muted-foreground bg-background px-1.5 py-0.5 rounded border border-border">{{ product.skus.length }} SKUs</span>
                                        </div>
                                    </td>
                                </tr>

                                <tr v-for="(sku, index) in product.skus" :key="sku.id" class="group hover:bg-muted/5 transition-colors">
                                    
                                    <td class="px-4 py-2 bg-card group-hover:bg-muted/5 sticky left-0 z-10 border-r border-border pl-12 shadow-[1px_0_0_0_rgba(0,0,0,0.02)]">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2 overflow-hidden">
                                                <div class="w-2 h-px bg-border absolute left-6"></div>
                                                <div class="w-px h-full bg-border absolute left-6 -top-1/2"></div>
                                                
                                                <div class="min-w-0">
                                                    <div class="font-medium text-foreground text-xs truncate max-w-[180px]" :title="sku.name">
                                                        {{ sku.name }}
                                                    </div>
                                                    <div class="text-[10px] text-muted-foreground font-mono mt-0.5">
                                                        {{ sku.code || '---' }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="w-6 h-6 rounded border border-border bg-muted/20 flex items-center justify-center shrink-0 overflow-hidden">
                                                <img v-if="sku.image_url" :src="getImageUrl(sku.image_url)" class="w-full h-full object-cover">
                                                <Tag v-else :size="10" class="text-muted-foreground"/>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-4 py-2 text-center bg-muted/5 group-hover:bg-muted/10 sticky left-80 z-10 border-r border-border">
                                        <span class="font-mono font-bold text-muted-foreground text-xs opacity-70">
                                            {{ sku.base_price }}
                                        </span>
                                    </td>

                                    <td v-for="branch in branches" :key="branch.id" class="p-1 border-r border-border/30 last:border-r-0">
                                        <button @click="openModal(sku, branch.id, product.name)"
                                                class="w-full h-10 rounded border border-transparent hover:border-primary/30 hover:bg-background hover:shadow-sm transition-all flex flex-col items-center justify-center relative group/cell">
                                            
                                            <template v-if="getCellData(sku, branch.id)">
                                                <span class="font-bold text-xs font-mono tracking-tight"
                                                      :class="isOfferActive(getCellData(sku, branch.id)) ? 'text-orange-600' : 'text-foreground'">
                                                    {{ getCellData(sku, branch.id).final_price }}
                                                </span>
                                                
                                                <div v-if="isOfferActive(getCellData(sku, branch.id))" 
                                                     class="absolute top-0.5 right-1 w-1.5 h-1.5 bg-orange-500 rounded-full animate-pulse">
                                                </div>
                                            </template>

                                            <template v-else>
                                                <span class="text-[10px] text-muted-foreground/20 group-hover/cell:text-primary transition-colors">--</span>
                                            </template>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <div v-if="isModalOpen" class="fixed inset-0 z-[100] flex items-end sm:items-center justify-center sm:p-4 animate-in fade-in duration-200">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="closeModal"></div>

            <div class="bg-background w-full sm:max-w-md sm:rounded-2xl rounded-t-2xl shadow-2xl overflow-hidden relative z-10 animate-in slide-in-from-bottom-10 sm:zoom-in-95 duration-200 flex flex-col max-h-[90vh]">
                <div class="px-5 py-4 border-b border-border bg-muted/10 flex justify-between items-center shrink-0">
                    <div>
                        <h3 class="font-bold text-foreground text-lg">Configurar Precio</h3>
                        <p class="text-xs text-muted-foreground mt-0.5 truncate max-w-[280px]">{{ editingLabel }}</p>
                    </div>
                    <button @click="closeModal" class="btn btn-ghost btn-sm w-8 h-8 p-0 rounded-full bg-muted/20 hover:bg-muted">
                        <X :size="18" />
                    </button>
                </div>

                <form @submit.prevent="submitPrice" class="p-5 space-y-6 overflow-y-auto custom-scrollbar">
                    
                    <div class="grid grid-cols-2 p-1 bg-muted rounded-xl">
                        <button type="button" @click="form.type = 'regular'"
                                class="py-2.5 text-xs font-bold rounded-lg transition-all text-center flex items-center justify-center gap-2"
                                :class="form.type === 'regular' ? 'bg-background text-foreground shadow-sm ring-1 ring-black/5' : 'text-muted-foreground hover:text-foreground'">
                            <DollarSign :size="14"/> Precio Regular
                        </button>
                        <button type="button" @click="form.type = 'offer'"
                                class="py-2.5 text-xs font-bold rounded-lg transition-all text-center flex items-center justify-center gap-2"
                                :class="form.type === 'offer' ? 'bg-orange-500 text-white shadow-md' : 'text-muted-foreground hover:text-foreground'">
                            <Percent :size="14" /> Oferta Temporal
                        </button>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-foreground">Precio Venta</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground font-bold">$</span>
                                <input v-model="form.final_price" type="number" step="0.01" 
                                       class="w-full pl-7 pr-3 py-3 bg-background border border-input rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary text-lg font-black text-foreground"
                                       placeholder="0.00" required>
                            </div>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-muted-foreground">Precio Lista</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground font-bold text-xs">$</span>
                                <input v-model="form.list_price" type="number" step="0.01" 
                                       class="w-full pl-7 pr-3 py-3 bg-background border border-input rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary text-sm font-medium text-muted-foreground"
                                       placeholder="0.00">
                            </div>
                        </div>
                    </div>

                    <div v-if="form.type === 'offer'" class="bg-orange-50 border border-orange-100 rounded-xl p-4 space-y-3 animate-in slide-in-from-top-2">
                        <div class="flex items-center gap-2 text-orange-800 text-xs font-bold">
                            <Clock :size="14" /> Vigencia de la Oferta
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label class="text-[10px] text-orange-600/80 font-bold uppercase">Desde</label>
                                <input type="date" v-model="form.valid_from" class="w-full text-xs bg-white border-orange-200 rounded-lg focus:ring-orange-500 py-2">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] text-orange-600/80 font-bold uppercase">Hasta</label>
                                <input type="date" v-model="form.valid_to" class="w-full text-xs bg-white border-orange-200 rounded-lg focus:ring-orange-500 py-2">
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 p-3 border border-border rounded-xl bg-card">
                        <input type="number" v-model="form.min_quantity" class="w-16 text-center font-bold border-border rounded-lg py-1.5 text-sm bg-background">
                        <div class="flex flex-col">
                            <span class="text-xs font-bold text-foreground">Cantidad Mínima</span>
                            <span class="text-[10px] text-muted-foreground">Para aplicar este precio</span>
                        </div>
                    </div>
                </form>

                <div class="p-5 border-t border-border bg-muted/5 flex gap-3 shrink-0">
                    <button @click="closeModal" class="flex-1 btn btn-ghost text-muted-foreground hover:bg-muted">
                        Cancelar
                    </button>
                    <button @click="submitPrice" :disabled="form.processing" 
                            class="flex-[2] btn btn-primary shadow-lg shadow-primary/20">
                        <span v-if="form.processing" class="loading loading-spinner loading-sm"></span>
                        <span v-else class="flex items-center gap-2"><Save :size="18"/> Guardar</span>
                    </button>
                </div>
            </div>
        </div>

    </AdminLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: hsl(var(--muted-foreground)/0.2); border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: hsl(var(--muted-foreground)/0.4); }
</style>