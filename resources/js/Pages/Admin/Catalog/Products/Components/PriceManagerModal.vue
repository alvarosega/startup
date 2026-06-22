<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    show: Boolean,
    sku: Object,
    branches: Array
});

const emit = defineEmits(['close']);

const loadingPrices = ref(false);
const processingPriceAction = ref(false);
const skuPrices = ref([]);
const priceErrors = ref({});

const priceForm = ref({
    branch_id: '',
    type: 'regular',
    list_price: 0,
    final_price: 0,
    min_quantity: 1,
    valid_from: '',
    valid_to: ''
});

const getStrategyBadgeClass = (type) => {
    switch (type) {
        case 'regular': return 'bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-400 border border-neutral-200 dark:border-neutral-700';
        case 'offer':
        case 'liquidation': return 'bg-rose-50 dark:bg-rose-950/30 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-800';
        case 'wholesale': return 'bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800';
        case 'member':
        case 'staff': return 'bg-sky-50 dark:bg-sky-950/30 text-sky-700 dark:text-sky-400 border border-sky-200 dark:border-sky-800';
        default: return 'bg-amber-50 dark:bg-amber-950/30 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800';
    }
};

// Mitigación del Datetime-Local Trap calculando el desfase exacto de la zona horaria del operador
const getLocalISOString = () => {
    const tzoffset = (new Date()).getTimezoneOffset() * 60000;
    return (new Date(Date.now() - tzoffset)).toISOString().slice(0, 16);
};

const resetForm = () => {
    priceErrors.value = {};
    priceForm.value.branch_id = props.branches?.[0]?.id || '';
    priceForm.value.type = 'regular';
    priceForm.value.list_price = props.sku?.base_price ? Number(props.sku.base_price) : 0;
    priceForm.value.final_price = props.sku?.base_price ? Number(props.sku.base_price) : 0;
    priceForm.value.min_quantity = 1;
    priceForm.value.valid_from = getLocalISOString();
    priceForm.value.valid_to = '';
};

const fetchPrices = async () => {
    loadingPrices.value = true;
    try {
        const response = await axios.get(route('admin.prices.show', props.sku.id));
        skuPrices.value = response.data.prices || [];
    } catch (error) {
        console.error('ERROR FETCH:', error);
        alert('Fallo crítico al compilar la matriz de precios del nodo.');
    } finally {
        loadingPrices.value = false;
    }
};

const submitPriceRule = async () => {
    processingPriceAction.value = true;
    priceErrors.value = {};
    try {
        const payload = { sku_id: props.sku.id, ...priceForm.value };
        const response = await axios.post(route('admin.prices.store'), payload);
        
        // Sincronización del estado global de Inertia ante mutaciones asíncronas de Axios
        await fetchPrices();
        router.reload({ preserveScroll: true });
        
        priceForm.value.list_price = props.sku?.base_price ? Number(props.sku.base_price) : 0;
        priceForm.value.final_price = props.sku?.base_price ? Number(props.sku.base_price) : 0;
    } catch (error) {
        if (error.response?.status === 422) {
            priceErrors.value = error.response.data.errors;
        } else {
            alert('Violación de restricción transaccional en el núcleo.');
        }
    } finally {
        processingPriceAction.value = false;
    }
};

const deletePriceRule = async (priceId) => {
    if (!confirm('¿Anular esta regla de precio en la sucursal seleccionada?')) return;
    try {
        await axios.delete(route('admin.prices.destroy', priceId));
        skuPrices.value = skuPrices.value.filter(p => p.id !== priceId);
        router.reload({ preserveScroll: true });
    } catch (error) {
        alert('No se pudo remover el nodo de precio seleccionado.');
    }
};

watch(() => props.show, (isOpen) => {
    if (isOpen && props.sku) {
        fetchPrices();
        resetForm();
    }
});
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 animate-fade-in">
        <div class="absolute inset-0 bg-neutral-950/40 dark:bg-neutral-950/60 transition-opacity" @click="emit('close')"></div>

        <div class="bg-white dark:bg-neutral-900 w-full max-w-5xl border border-neutral-200 dark:border-neutral-800 rounded-md shadow-2xl flex flex-col max-h-[85vh] overflow-hidden text-neutral-900 dark:text-neutral-50 relative z-10">
            
            <div class="p-4 border-b border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-900/50 flex justify-between items-center shrink-0 select-none">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-rounded text-neutral-900 dark:text-white text-xl">monetization_on</span>
                    <div>
                        <h2 class="text-xs font-black uppercase tracking-wider text-neutral-900 dark:text-neutral-50">Estrategias Comerciales de Venta</h2>
                        <p class="text-[10px] text-neutral-400 dark:text-neutral-500 font-mono uppercase tracking-tight mt-0.5">
                            Variante: {{ sku?.name }} | EAN: {{ sku?.code }}
                        </p>
                    </div>
                </div>
                <button @click="emit('close')" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300 p-1 rounded-md hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors flex items-center justify-center border border-neutral-200 dark:border-neutral-800">
                    <span class="material-symbols-rounded text-base">close</span>
                </button>
            </div>

            <div class="p-5 overflow-y-auto flex-1 grid grid-cols-1 lg:grid-cols-12 gap-5">
                
                <div class="lg:col-span-8 space-y-2">
                    <h3 class="text-[10px] font-mono font-black text-neutral-400 dark:text-neutral-500 uppercase tracking-widest pl-1">Reglas Corporativas Vigentes</h3>
                    
                    <div v-if="loadingPrices" class="p-16 flex flex-col items-center justify-center text-neutral-400 dark:text-neutral-500 gap-2 font-mono text-xs">
                        <span class="material-symbols-rounded text-xl text-neutral-900 dark:text-white animate-spin">progress_activity</span>
                        <span class="uppercase tracking-wider">Sincronizando Matriz...</span>
                    </div>
                    
                    <div v-else class="w-full border border-neutral-200 dark:border-neutral-800 rounded-md bg-white dark:bg-neutral-900 overflow-hidden">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-neutral-50/70 dark:bg-neutral-900/50 border-b border-neutral-200 dark:border-neutral-800 text-[10px] font-mono font-bold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">
                                    <th class="p-2.5">SUCURSAL / ÁREA</th>
                                    <th class="p-2.5">ESTRATEGIA</th>
                                    <th class="p-2.5 text-right font-mono">P. LISTA</th>
                                    <th class="p-2.5 text-right font-mono">P. FINAL</th>
                                    <th class="p-2.5 w-14 text-center select-none">MÍN</th>
                                    <th class="p-2.5 w-14 text-center select-none">ACC</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800 font-mono text-xs">
                                <tr v-for="price in skuPrices" :key="price.id" class="hover:bg-neutral-50/40 dark:hover:bg-neutral-800/20 transition-colors">
                                    <td class="p-2.5 font-bold text-neutral-900 dark:text-neutral-100 uppercase select-all">{{ price.branch_name }}</td>
                                    <td class="p-2.5 select-none">
                                        <span :class="getStrategyBadgeClass(price.type)" class="text-[9px] px-1.5 py-0.5 font-bold uppercase tracking-tight rounded-sm">
                                            {{ price.type }}
                                        </span>
                                    </td>
                                    <td class="p-2.5 text-right text-neutral-400 dark:text-neutral-500 select-all">Bs. {{ Number(price.list_price).toFixed(2) }}</td>
                                    <td class="p-2.5 text-right text-neutral-900 dark:text-neutral-50 font-bold select-all">Bs. {{ Number(price.final_price).toFixed(2) }}</td>
                                    <td class="p-2.5 text-center text-neutral-400 dark:text-neutral-500 select-none">{{ price.min_quantity }}</td>
                                    <td class="p-2.5 text-center select-none">
                                        <button @click="deletePriceRule(price.id)" class="text-neutral-300 hover:text-rose-600 dark:hover:text-rose-400 p-1 rounded-sm hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-colors flex items-center justify-center mx-auto">
                                            <span class="material-symbols-rounded text-base">delete_forever</span>
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="skuPrices.length === 0">
                                    <td colspan="6" class="p-8 text-center text-neutral-400 dark:text-neutral-500 font-sans text-xs italic uppercase tracking-wider select-none bg-neutral-50/20 dark:bg-neutral-900/5">
                                        No existen reglas activas. Fallback base del maestro asignado: Bs. {{ Number(sku?.base_price).toFixed(2) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="lg:col-span-4 bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded-md p-4 space-y-4 h-fit">
                    <h3 class="text-[10px] font-mono font-black text-neutral-900 dark:text-white uppercase tracking-widest border-b border-neutral-200 dark:border-neutral-800 pb-2 flex items-center gap-1.5">
                        <span class="material-symbols-rounded text-sm text-neutral-900 dark:text-white">add_moderator</span>
                        <span>Inyectar Regla</span>
                    </h3>
                    
                    <div class="space-y-3 font-mono text-[11px]">
                        <div class="space-y-1">
                            <label class="font-bold text-neutral-400 dark:text-neutral-500 uppercase">Sucursal Operativa *</label>
                            <select v-model="priceForm.branch_id" class="w-full bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-md px-2 py-1 text-xs font-bold uppercase text-neutral-900 dark:text-neutral-50 focus:border-neutral-400 dark:focus:border-neutral-700 focus:ring-0 outline-none transition-colors">
                                <option v-for="branch in branches" :key="branch.id" :value="branch.id">{{ branch.name.toUpperCase() }}</option>
                            </select>
                            <span v-if="priceErrors.branch_id" class="text-[9px] font-mono text-rose-600 dark:text-rose-400 block mt-0.5 uppercase font-bold">{{ priceErrors.branch_id[0] }}</span>
                        </div>
                        
                        <div class="space-y-1">
                            <label class="font-bold text-neutral-400 dark:text-neutral-500 uppercase">Estrategia de Margen *</label>
                            <select v-model="priceForm.type" class="w-full bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-md px-2 py-1 text-xs font-bold uppercase text-neutral-900 dark:text-neutral-50 focus:border-neutral-400 dark:focus:border-neutral-700 focus:ring-0 outline-none transition-colors">
                                <option value="regular">REGULAR_BASE</option>
                                <option value="offer">OFERTA_LIQUIDA</option>
                                <option value="member">SOCIO_EXCLUSIVO</option>
                                <option value="wholesale">MAYORISTA_DIST</option>
                                <option value="liquidation">LIQUIDACIÓN_STOCK</option>
                                <option value="staff">PERSONAL_INTERNO</option>
                            </select>
                            <span v-if="priceErrors.type" class="text-[9px] font-mono text-rose-600 dark:text-rose-400 block mt-0.5 uppercase font-bold">{{ priceErrors.type[0] }}</span>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-2">
                            <div class="space-y-1">
                                <label class="font-bold text-neutral-400 dark:text-neutral-500 uppercase">P. Lista *</label>
                                <input v-model.number="priceForm.list_price" type="number" step="0.01" class="w-full bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-md px-2 py-1 text-right text-xs text-neutral-900 dark:text-neutral-50 focus:border-neutral-400 dark:focus:border-neutral-700 focus:ring-0 outline-none transition-colors" />
                                <span v-if="priceErrors.list_price" class="text-[9px] font-mono text-rose-600 dark:text-rose-400 block mt-0.5 uppercase font-bold">{{ priceErrors.list_price[0] }}</span>
                            </div>
                            <div class="space-y-1">
                                <label class="font-bold text-neutral-400 dark:text-neutral-500 uppercase">P. Final *</label>
                                <input v-model.number="priceForm.final_price" type="number" step="0.01" class="w-full bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-md px-2 py-1 text-right text-xs font-bold text-neutral-900 dark:text-neutral-50 focus:border-neutral-400 dark:focus:border-neutral-700 focus:ring-0 outline-none transition-colors" />
                                <span v-if="priceErrors.final_price" class="text-[9px] font-mono text-rose-600 dark:text-rose-400 block mt-0.5 uppercase font-bold">{{ priceErrors.final_price[0] }}</span>
                            </div>
                        </div>
                        
                        <div class="space-y-1">
                            <label class="font-bold text-neutral-400 dark:text-neutral-500 uppercase">Volumen Mínimo *</label>
                            <input v-model.number="priceForm.min_quantity" type="number" min="1" class="w-full bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-md px-2 py-1 text-right text-xs text-neutral-900 dark:text-neutral-50 focus:border-neutral-400 dark:focus:border-neutral-700 focus:ring-0 outline-none transition-colors" />
                            <span v-if="priceErrors.min_quantity" class="text-[9px] font-mono text-rose-600 dark:text-rose-400 block mt-0.5 uppercase font-bold">{{ priceErrors.min_quantity[0] }}</span>
                        </div>
                        
                        <div class="space-y-1">
                            <label class="font-bold text-neutral-400 dark:text-neutral-500 uppercase">Apertura Transmisión *</label>
                            <input v-model="priceForm.valid_from" type="datetime-local" class="w-full bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-md px-2 py-1 text-xs text-neutral-900 dark:text-neutral-50 focus:border-neutral-400 dark:focus:border-neutral-700 focus:ring-0 outline-none transition-colors" />
                            <span v-if="priceErrors.valid_from" class="text-[9px] font-mono text-rose-600 dark:text-rose-400 block mt-0.5 uppercase font-bold">{{ priceErrors.valid_from[0] }}</span>
                        </div>
                        
                        <div class="space-y-1">
                            <label class="font-bold text-neutral-400 dark:text-neutral-500 uppercase">Cierre Transmisión</label>
                            <input v-model="priceForm.valid_to" type="datetime-local" class="w-full bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-md px-2 py-1 text-xs text-neutral-900 dark:text-neutral-50 focus:border-neutral-400 dark:focus:border-neutral-700 focus:ring-0 outline-none transition-colors" placeholder="Eterno si se omite" />
                            <span v-if="priceErrors.valid_to" class="text-[9px] font-mono text-rose-600 dark:text-rose-400 block mt-0.5 uppercase font-bold">{{ priceErrors.valid_to[0] }}</span>
                        </div>
                        
                        <button type="button" @click="submitPriceRule" :disabled="processingPriceAction" 
                                class="w-full bg-neutral-900 hover:bg-neutral-800 dark:bg-neutral-50 dark:hover:bg-neutral-200 text-white dark:text-neutral-950 py-2.5 font-sans font-bold uppercase text-xs tracking-wider border border-transparent rounded-md transition-colors inline-flex items-center justify-center gap-1.5 disabled:opacity-50">
                            <span class="material-symbols-rounded text-sm">publish</span>
                            <span>{{ processingPriceAction ? 'Procesando...' : 'Aplicar Regla' }}</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="px-5 py-3 border-t border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-900/50 flex items-center justify-end select-none shrink-0">
                <button type="button" @click="emit('close')" class="px-4 py-1.5 border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 rounded-md hover:bg-neutral-100 dark:hover:bg-neutral-800 text-xs font-bold uppercase tracking-wider transition-colors text-neutral-900 dark:text-neutral-50">
                    Finalizar Consulta
                </button>
            </div>
        </div>
    </div>
</template>