<script setup>
import { ref, watch, computed } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
    Search, MapPin, Filter, Tag, Calendar, 
    AlertCircle, CheckCircle, X, Save, Globe, Clock
} from 'lucide-vue-next';
import debounce from 'lodash/debounce';

const props = defineProps({
    products: Object, // Paginado, contiene SKUs y prices_matrix
    branches: Array,
    filters: Object
});

// --- FILTROS ---
const search = ref(props.filters.search || '');

watch(search, debounce((val) => {
    router.get(route('admin.prices.index'), { search: val }, { 
        preserveState: true, 
        replace: true, 
        preserveScroll: true 
    });
}, 500));

// --- MODAL DE EDICIÓN ---
const isModalOpen = ref(false);
const editingLabel = ref(''); // "Sucursal Centro - Coca Cola 3L"

// Formulario reactivo para Crear/Editar
const form = useForm({
    sku_id: null,
    branch_id: null,
    final_price: '',
    list_price: '',
    type: 'regular', // regular | offer
    min_quantity: 1,
    valid_from: new Date().toISOString().slice(0, 10),
    valid_to: null,
});

const openModal = (sku, branchId) => {
    const branchName = props.branches.find(b => b.id === branchId)?.name || 'Sucursal';
    editingLabel.value = `${branchName} • ${sku.name}`;
    
    // Buscar si ya existe precio para esta celda
    const existingPrices = sku.prices_matrix[branchId]; // Array de precios
    
    // Lógica simple: Tomamos el precio vigente más relevante (Offer > Regular)
    // En tu backend ya ordenamos, así que tomamos el último (o el primero según tu lógica)
    // Aquí asumimos que sku.prices_matrix[branchId] devuelve una colección.
    // Si tu backend devuelve un objeto único, ajusta esto.
    
    const activePrice = Array.isArray(existingPrices) && existingPrices.length > 0 
        ? existingPrices[existingPrices.length - 1] 
        : null;

    if (activePrice) {
        // MODO EDICIÓN
        form.sku_id = sku.id;
        form.branch_id = branchId;
        form.final_price = activePrice.final_price;
        form.list_price = activePrice.list_price;
        form.type = activePrice.type;
        form.min_quantity = activePrice.min_quantity;
        form.valid_from = activePrice.valid_from ? activePrice.valid_from.substring(0, 10) : '';
        form.valid_to = activePrice.valid_to ? activePrice.valid_to.substring(0, 10) : '';
    } else {
        // MODO CREACIÓN (Heredar precio base)
        form.sku_id = sku.id;
        form.branch_id = branchId;
        form.final_price = sku.base_price; // Sugerir base price
        form.list_price = (sku.base_price * 1.10).toFixed(2); // Sugerir un list price
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

// --- HELPERS VISUALES ---
const getCellData = (sku, branchId) => {
    const prices = sku.prices_matrix[branchId];
    if (!prices || prices.length === 0) return null;
    
    // Prioridad visual: Mostrar Oferta si existe, sino Regular
    const offer = prices.find(p => p.type === 'offer');
    return offer || prices[0]; // Retorna el objeto precio
};

const isOfferActive = (priceObj) => {
    return priceObj && priceObj.type === 'offer';
};
</script>

<template>
    <AdminLayout>
        <Head title="Gestión de Precios" />

        <div class="h-[calc(100vh-100px)] flex flex-col">
            
            <div class="mb-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-black text-gray-800 tracking-tight">Matriz de Precios</h1>
                    <p class="text-sm text-gray-500">Configura precios regulares y ofertas por sucursal</p>
                </div>
                
                <div class="relative w-full sm:w-80">
                    <Search class="absolute left-3 top-2.5 text-gray-400" :size="18" />
                    <input 
                        v-model="search" 
                        type="text" 
                        placeholder="Buscar producto, SKU o código..." 
                        class="w-full pl-10 pr-4 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                    >
                </div>
            </div>

            <div class="flex-1 bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden flex flex-col">
                <div class="overflow-auto flex-1 relative">
                    <table class="w-full border-collapse">
                        <thead class="bg-gray-50 sticky top-0 z-10 shadow-sm">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider bg-gray-50 sticky left-0 z-20 w-64 border-b border-r border-gray-200">
                                    Producto / SKU
                                </th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider bg-gray-50 sticky left-64 z-20 w-32 border-b border-r border-gray-200">
                                    <div class="flex items-center justify-center gap-1">
                                        <Globe :size="14" /> Base
                                    </div>
                                </th>
                                <th 
                                    v-for="branch in branches" 
                                    :key="branch.id"
                                    class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider min-w-[140px] border-b border-gray-200"
                                >
                                    {{ branch.name }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <template v-for="product in products.data" :key="product.id">
                                <tr v-for="(sku, index) in product.skus" :key="sku.id" class="group hover:bg-blue-50/30 transition-colors">
                                    
                                    <td class="px-4 py-3 bg-white group-hover:bg-blue-50/30 sticky left-0 z-10 border-r border-gray-200">
                                        <div class="flex items-center gap-3">
                                            <div v-if="index === 0" class="w-8 h-8 rounded bg-gray-100 border border-gray-200 shrink-0 overflow-hidden">
                                                <img v-if="product.image_url" :src="product.image_url" class="w-full h-full object-cover">
                                            </div>
                                            <div v-else class="w-8 h-8 shrink-0"></div> <div class="min-w-0">
                                                <div v-if="index === 0" class="font-bold text-gray-800 text-sm truncate" :title="product.name">
                                                    {{ product.name }}
                                                </div>
                                                <div class="text-xs text-gray-500 flex items-center gap-2">
                                                    <span class="font-mono bg-gray-100 px-1 rounded text-[10px]">{{ sku.code || '---' }}</span>
                                                    <span class="truncate">{{ sku.name }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-4 py-3 text-center bg-gray-50/50 group-hover:bg-blue-50/30 sticky left-64 z-10 border-r border-gray-200">
                                        <span class="font-mono font-bold text-gray-600 text-sm">
                                            {{ sku.base_price }}
                                        </span>
                                    </td>

                                    <td 
                                        v-for="branch in branches" 
                                        :key="branch.id"
                                        class="px-2 py-2 text-center border-r border-gray-50 last:border-r-0"
                                    >
                                        <button 
                                            @click="openModal(sku, branch.id)"
                                            class="w-full h-full min-h-[40px] rounded-lg border flex flex-col items-center justify-center transition-all duration-200 relative overflow-hidden group/cell"
                                            :class="[
                                                getCellData(sku, branch.id) 
                                                    ? (isOfferActive(getCellData(sku, branch.id)) ? 'bg-orange-50 border-orange-200 hover:border-orange-400' : 'bg-white border-gray-200 hover:border-blue-400')
                                                    : 'bg-gray-50/50 border-transparent hover:border-gray-300 hover:bg-white'
                                            ]"
                                        >
                                            <template v-if="getCellData(sku, branch.id)">
                                                <div v-if="isOfferActive(getCellData(sku, branch.id))" class="absolute top-0 right-0">
                                                    <div class="bg-orange-500 text-white text-[8px] px-1 font-bold rounded-bl">OFERTA</div>
                                                </div>

                                                <span 
                                                    class="font-bold text-sm"
                                                    :class="isOfferActive(getCellData(sku, branch.id)) ? 'text-orange-600' : 'text-gray-800'"
                                                >
                                                    {{ getCellData(sku, branch.id).final_price }}
                                                </span>
                                                
                                                <span v-if="isOfferActive(getCellData(sku, branch.id))" class="text-[10px] text-gray-400 line-through -mt-1">
                                                    {{ getCellData(sku, branch.id).list_price }}
                                                </span>
                                            </template>

                                            <template v-else>
                                                <span class="text-gray-300 group-hover/cell:text-gray-400 font-bold text-lg opacity-0 group-hover/cell:opacity-100 transition-opacity">
                                                    +
                                                </span>
                                                <span class="text-[10px] text-gray-300 absolute bottom-1 group-hover/cell:opacity-0">
                                                    ---
                                                </span>
                                            </template>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                
                <div class="bg-gray-50 px-4 py-3 border-t border-gray-200 flex justify-end">
                    </div>
            </div>
        </div>

        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm animate-in fade-in duration-200">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden animate-in zoom-in-95 duration-200">
                
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-100 flex justify-between items-center">
                    <div>
                        <h3 class="font-bold text-gray-800">Configurar Precio</h3>
                        <p class="text-xs text-gray-500 mt-0.5 truncate max-w-[250px]">{{ editingLabel }}</p>
                    </div>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
                        <X :size="20" />
                    </button>
                </div>

                <form @submit.prevent="submitPrice" class="p-6 space-y-5">
                    
                    <div class="grid grid-cols-2 gap-3 p-1 bg-gray-100 rounded-lg">
                        <button 
                            type="button"
                            @click="form.type = 'regular'"
                            class="py-2 text-xs font-bold rounded-md transition-all text-center"
                            :class="form.type === 'regular' ? 'bg-white text-gray-800 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                        >
                            Regular
                        </button>
                        <button 
                            type="button"
                            @click="form.type = 'offer'"
                            class="py-2 text-xs font-bold rounded-md transition-all text-center flex items-center justify-center gap-2"
                            :class="form.type === 'offer' ? 'bg-orange-500 text-white shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                        >
                            <Tag :size="12" /> Oferta Temporal
                        </button>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-1">
                            <label class="block text-xs font-bold text-gray-700 mb-1">Precio Venta</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-gray-400 text-xs">Bs</span>
                                <input 
                                    v-model="form.final_price"
                                    type="number" step="0.01" 
                                    class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 font-bold text-lg text-gray-800"
                                    placeholder="0.00"
                                    required
                                >
                            </div>
                        </div>

                        <div class="col-span-1">
                            <label class="block text-xs font-bold text-gray-500 mb-1">Precio Lista (Tachado)</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-gray-400 text-xs">Bs</span>
                                <input 
                                    v-model="form.list_price"
                                    type="number" step="0.01" 
                                    class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-gray-500"
                                    placeholder="0.00"
                                >
                            </div>
                        </div>
                    </div>

                    <div v-if="form.type === 'offer'" class="bg-orange-50 border border-orange-100 rounded-lg p-4 space-y-3 animate-in slide-in-from-top-2">
                        <div class="flex items-center gap-2 text-orange-700 text-xs font-bold mb-1">
                            <Calendar :size="14" /> Vigencia de la Oferta
                        </div>
                        
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-[10px] text-gray-500 font-bold uppercase">Desde</label>
                                <input type="date" v-model="form.valid_from" class="w-full text-xs border-orange-200 rounded focus:ring-orange-500">
                            </div>
                            <div>
                                <label class="text-[10px] text-gray-500 font-bold uppercase">Hasta</label>
                                <input type="date" v-model="form.valid_to" class="w-full text-xs border-orange-200 rounded focus:ring-orange-500">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="flex items-center gap-2 text-xs text-gray-500">
                            <input type="number" v-model="form.min_quantity" class="w-14 text-xs border-gray-300 rounded text-center">
                            <span>Unidades mínimas para aplicar este precio</span>
                        </label>
                    </div>

                </form>

                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                    <button 
                        @click="closeModal" 
                        class="px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-200 rounded-lg transition"
                    >
                        Cancelar
                    </button>
                    <button 
                        @click="submitPrice" 
                        :disabled="form.processing"
                        class="px-4 py-2 text-sm font-bold text-white bg-gray-900 hover:bg-blue-600 rounded-lg shadow-lg hover:shadow-xl transition disabled:opacity-50 flex items-center gap-2"
                    >
                        <Save :size="16" />
                        Guardar Precio
                    </button>
                </div>
            </div>
        </div>

    </AdminLayout>
</template>

<style scoped>
/* Ocultar scrollbars pero permitir scroll */
.overflow-auto::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}
.overflow-auto::-webkit-scrollbar-track {
    background: #f1f1f1; 
}
.overflow-auto::-webkit-scrollbar-thumb {
    background: #ccc; 
    border-radius: 4px;
}
.overflow-auto::-webkit-scrollbar-thumb:hover {
    background: #999; 
}
</style>