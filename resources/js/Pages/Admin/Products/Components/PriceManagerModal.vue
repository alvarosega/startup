<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';
import { Coins, X, Loader2, Trash2 } from 'lucide-vue-next';

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
    valid_from: new Date().toISOString().slice(0, 16),
    valid_to: ''
});

watch(() => props.show, (newVal) => {
    if (newVal && props.sku) {
        fetchPrices();
        resetForm();
    }
});

const resetForm = () => {
    priceErrors.value = {};
    priceForm.value.branch_id = props.branches?.[0]?.id || '';
    priceForm.value.list_price = props.sku.base_price;
    priceForm.value.final_price = props.sku.base_price;
    priceForm.value.min_quantity = 1;
};

const fetchPrices = async () => {
    loadingPrices.value = true;
    try {
        const response = await axios.get(route('admin.prices.show', props.sku.id));
        skuPrices.value = response.data.prices;
    } catch (error) {
        console.error('ERROR FETCH:', error);
        alert('Error al compilar la matriz de precios del servidor.');
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
        
        if (response.data.success) {
            await fetchPrices();
            priceForm.value.list_price = props.sku.base_price;
            priceForm.value.final_price = props.sku.base_price;
        }
    } catch (error) {
        console.error('ERROR SUBMIT:', error);
        if (error.response?.status === 422) {
            priceErrors.value = error.response.data.errors;
        } else {
            alert('Violación de restricción transaccional en el servidor.');
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
    } catch (error) {
        console.error('ERROR DELETE:', error);
        alert('No se pudo remover el nodo de precio seleccionado.');
    }
};

const close = () => {
    emit('close');
};
</script>
<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
        <div class="bg-card w-full max-w-4xl border border-border rounded-xl shadow-xl flex flex-col max-h-[85vh] overflow-hidden animate-in fade-in zoom-in-95 duration-150">
            <div class="p-4 border-b border-border bg-muted/40 flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <Coins class="text-primary" :size="20" />
                    <div>
                        <h2 class="text-sm font-bold text-foreground uppercase tracking-tight">Estrategias Comerciales de Venta</h2>
                        <p class="text-[11px] text-muted-foreground font-mono">Variante: {{ sku?.name }} | EAN: {{ sku?.code }}</p>
                    </div>
                </div>
                <button @click="close" class="p-1.5 text-muted-foreground hover:text-foreground hover:bg-muted rounded-md transition-colors">
                    <X :size="16" />
                </button>
            </div>

            <div class="p-6 overflow-y-auto space-y-6 flex-1 grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-3">
                    <h3 class="text-xs font-bold text-muted-foreground uppercase tracking-wider">Reglas Vigentes</h3>
                    <div v-if="loadingPrices" class="p-12 flex flex-col items-center justify-center text-muted-foreground gap-2">
                        <Loader2 class="animate-spin text-primary" :size="24" />
                    </div>
                    <div v-else class="overflow-hidden border border-border/60 rounded-lg">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead class="bg-muted font-semibold text-muted-foreground border-b border-border">
                                <tr>
                                    <th class="p-2.5">Sucursal</th>
                                    <th class="p-2.5">Estrategia</th>
                                    <th class="p-2.5 text-right">P. Lista</th>
                                    <th class="p-2.5 text-right">P. Final</th>
                                    <th class="p-2.5 text-center">Mín</th>
                                    <th class="p-2.5 text-right">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <tr v-for="price in skuPrices" :key="price.id" class="hover:bg-muted/10">
                                    <td class="p-2.5 font-semibold text-foreground uppercase">{{ price.branch_name }}</td>
                                    <td class="p-2.5"><span class="px-1.5 py-0.5 bg-primary/10 text-primary text-[10px] rounded font-bold uppercase">{{ price.type }}</span></td>
                                    <td class="p-2.5 text-right font-mono text-muted-foreground">{{ Number(price.list_price).toFixed(2) }}</td>
                                    <td class="p-2.5 text-right font-mono font-bold text-foreground">{{ Number(price.final_price).toFixed(2) }}</td>
                                    <td class="p-2.5 text-center font-mono">{{ price.min_quantity }}</td>
                                    <td class="p-2.5 text-right">
                                        <button @click="deletePriceRule(price.id)" class="text-destructive hover:bg-destructive/10 p-1 rounded">
                                            <Trash2 :size="13" />
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="skuPrices.length === 0">
                                    <td colspan="6" class="p-6 text-center text-muted-foreground italic">No hay reglas asignadas. Fallback sugerido: {{ Number(sku?.base_price).toFixed(2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-muted/30 border border-border rounded-xl p-4 h-fit space-y-4">
                    <h3 class="text-xs font-bold text-muted-foreground uppercase tracking-wider">Inyectar Regla</h3>
                    <div class="space-y-3 text-xs">
                        <div>
                            <label class="block font-semibold text-muted-foreground mb-1">Sucursal *</label>
                            <select v-model="priceForm.branch_id" class="w-full bg-background border border-border rounded px-2.5 py-1.5 outline-none focus:ring-1 focus:ring-primary">
                                <option v-for="branch in branches" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
                            </select>
                            <p v-if="priceErrors.branch_id" class="text-destructive text-[10px]">{{ priceErrors.branch_id[0] }}</p>
                        </div>
                        <div>
                            <label class="block font-semibold text-muted-foreground mb-1">Estrategia *</label>
                            <select v-model="priceForm.type" class="w-full bg-background border border-border rounded px-2.5 py-1.5 outline-none focus:ring-1 focus:ring-primary">
                                <option value="regular">Regular</option>
                                <option value="offer">Oferta</option>
                                <option value="member">Socio</option>
                                <option value="wholesale">Mayorista</option>
                                <option value="liquidation">Liquidación</option>
                                <option value="staff">Personal</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block font-semibold text-muted-foreground mb-1">P. Lista *</label>
                                <input v-model.number="priceForm.list_price" type="number" step="0.01" class="w-full bg-background border border-border rounded px-2.5 py-1.5 font-mono text-right outline-none focus:ring-1 focus:ring-primary" />
                            </div>
                            <div>
                                <label class="block font-semibold text-muted-foreground mb-1">P. Final *</label>
                                <input v-model.number="priceForm.final_price" type="number" step="0.01" class="w-full bg-background border border-border rounded px-2.5 py-1.5 font-mono text-right outline-none focus:ring-1 focus:ring-primary" />
                            </div>
                        </div>
                        <div>
                            <label class="block font-semibold text-muted-foreground mb-1">Cant. Mínima *</label>
                            <input v-model.number="priceForm.min_quantity" type="number" min="1" class="w-full bg-background border border-border rounded px-2.5 py-1.5 font-mono text-right outline-none focus:ring-1 focus:ring-primary" />
                        </div>
                        <div>
                            <label class="block font-semibold text-muted-foreground mb-1">Inicio Vigencia *</label>
                            <input v-model="priceForm.valid_from" type="datetime-local" class="w-full bg-background border border-border rounded px-2.5 py-1.5 outline-none focus:ring-1 focus:ring-primary" />
                        </div>
                        <div>
                            <label class="block font-semibold text-muted-foreground mb-1">Fin Vigencia</label>
                            <input v-model="priceForm.valid_to" type="datetime-local" class="w-full bg-background border border-border rounded px-2.5 py-1.5 outline-none focus:ring-1 focus:ring-primary" />
                        </div>
                        <button @click="submitPriceRule" :disabled="processingPriceAction" class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-primary text-primary-foreground font-medium rounded hover:bg-primary/90 disabled:opacity-50">
                            <Loader2 v-if="processingPriceAction" class="animate-spin" :size="14" />
                            Aplicar Regla
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>