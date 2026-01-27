<script setup>
import { ref, computed, watch } from 'vue';
import axios from 'axios';
import { useForm } from '@inertiajs/vue3';
import { X, ShoppingBag, Plus, Minus, AlertTriangle, Loader2 } from 'lucide-vue-next';

const props = defineProps({
    show: Boolean,
    bundleSlug: String,
});

const emit = defineEmits(['close']);

const loading = ref(false);
const bundle = ref(null);
const items = ref([]); // Lista local reactiva de items a agregar
const multiplier = ref(1);
const stockWarnings = ref([]); // Para avisar si hubo recorte automático

// Cargar datos al abrir
watch(() => props.show, async (newVal) => {
    if (newVal && props.bundleSlug) {
        loading.value = true;
        stockWarnings.value = [];
        multiplier.value = 1;
        
        try {
            const response = await axios.get(route('bundles.show', props.bundleSlug));
            bundle.value = response.data.bundle;
            
            // Inicializar items y aplicar lógica de "Auto-Sanación" inicial
            items.value = response.data.items.map(item => {
                let initialQty = item.default_quantity;
                
                // REGLA: Si en el bundle tenemos 10, pero solo hay 5, lo editamos a 5.
                if (initialQty > item.max_stock) {
                    stockWarnings.value.push(`El producto "${item.name}" se ajustó a ${item.max_stock} (Stock máx).`);
                    initialQty = item.max_stock;
                }

                return {
                    ...item,
                    base_quantity: initialQty, // Cantidad base por 1 Bundle (ajustada a stock)
                    quantity: initialQty,      // Cantidad actual (afectada por multi)
                };
            });

        } catch (error) {
            console.error(error);
            emit('close');
        } finally {
            loading.value = false;
        }
    }
});

// Watcher del Multiplicador
watch(multiplier, (val) => {
    if (!items.value.length) return;

    items.value.forEach(item => {
        let newQty = item.base_quantity * val;
        
        // Validación de Stock al multiplicar
        if (newQty > item.max_stock) {
            newQty = item.max_stock;
        }
        item.quantity = newQty;
    });
});

// Update individual (Rompe la sincronía estricta del multiplicador base, pero es necesario)
const updateItemQty = (item, newQty) => {
    if (newQty < 0) return;
    if (newQty > item.max_stock) return; // Bloqueo UI
    item.quantity = newQty;
};

// Cálculo de Total
const totalPrice = computed(() => {
    return items.value.reduce((acc, item) => acc + (item.quantity * item.unit_price), 0);
});

// Enviar al Carrito
const form = useForm({
    items: []
});

const addToCart = () => {
    // Filtramos items con cantidad > 0
    const payload = items.value
        .filter(i => i.quantity > 0)
        .map(i => ({ sku_id: i.sku_id, quantity: i.quantity }));

    if (payload.length === 0) return alert('El paquete está vacío.');

    form.items = payload;
    
    form.post(route('cart.bulk-store'), {
        preserveScroll: true,
        onSuccess: () => emit('close'),
    });
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm" @click.self="emit('close')">
        
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden flex flex-col max-h-[90vh]">
            
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <h2 class="font-black text-gray-800 text-lg">
                    <span v-if="loading">Cargando...</span>
                    <span v-else>{{ bundle?.name }}</span>
                </h2>
                <button @click="emit('close')" class="p-2 hover:bg-gray-200 rounded-full transition"><X :size="20"/></button>
            </div>

            <div class="flex-1 overflow-y-auto p-6 relative">
                
                <div v-if="loading" class="absolute inset-0 flex items-center justify-center bg-white/80 z-10">
                    <Loader2 class="animate-spin text-blue-600" :size="32"/>
                </div>

                <div v-if="stockWarnings.length > 0" class="mb-4 bg-yellow-50 p-3 rounded-lg border border-yellow-200">
                    <div class="flex gap-2 items-start">
                        <AlertTriangle class="text-yellow-600 shrink-0" :size="16" />
                        <ul class="text-xs text-yellow-700 list-disc pl-4 space-y-1">
                            <li v-for="(warn, i) in stockWarnings" :key="i">{{ warn }}</li>
                        </ul>
                    </div>
                </div>

                <div class="mb-6 flex items-center justify-between bg-blue-50 p-4 rounded-xl border border-blue-100">
                    <span class="font-bold text-blue-800 text-sm">Cantidad de Packs:</span>
                    <div class="flex items-center gap-3 bg-white rounded-lg shadow-sm px-2 py-1">
                        <button @click="multiplier = Math.max(1, multiplier - 1)" class="w-8 h-8 flex items-center justify-center hover:bg-gray-100 rounded-full font-bold text-gray-600">-</button>
                        <span class="font-black text-lg w-8 text-center">{{ multiplier }}</span>
                        <button @click="multiplier++" class="w-8 h-8 flex items-center justify-center hover:bg-gray-100 rounded-full font-bold text-blue-600">+</button>
                    </div>
                </div>

                <div class="space-y-4">
                    <div v-for="item in items" :key="item.sku_id" class="flex gap-4 items-center">
                        <div class="w-14 h-14 bg-gray-50 rounded-lg shrink-0 flex items-center justify-center">
                            <img :src="item.image" class="w-10 h-10 object-contain mix-blend-multiply">
                        </div>
                        
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-sm text-gray-800 truncate">{{ item.name }}</p>
                            <p class="text-xs text-gray-500">Stock: {{ item.max_stock }}</p>
                        </div>

                        <div class="flex flex-col items-end gap-1">
                            <div class="flex items-center border border-gray-200 rounded-lg">
                                <button @click="updateItemQty(item, item.quantity - 1)" :disabled="item.quantity <= 0" class="px-2 py-1 hover:bg-gray-50 disabled:opacity-50"><Minus :size="12"/></button>
                                <span class="text-xs font-bold w-6 text-center">{{ item.quantity }}</span>
                                <button @click="updateItemQty(item, item.quantity + 1)" :disabled="item.quantity >= item.max_stock" class="px-2 py-1 hover:bg-gray-50 disabled:opacity-50"><Plus :size="12"/></button>
                            </div>
                            <span v-if="item.quantity >= item.max_stock" class="text-[9px] text-red-500 font-bold">Max Stock</span>
                        </div>
                    </div>
                </div>

            </div>

            <div class="p-6 border-t border-gray-100 bg-gray-50">
                <div class="flex justify-between items-center mb-4">
                    <span class="text-gray-600 font-medium">Total Estimado</span>
                    <span class="text-xl font-black text-blue-600">Bs {{ totalPrice.toFixed(2) }}</span>
                </div>
                
                <button @click="addToCart" :disabled="form.processing || items.every(i => i.quantity === 0)"
                    class="w-full bg-blue-600 text-white font-bold py-3.5 rounded-xl shadow-lg hover:bg-blue-700 transition active:scale-95 disabled:opacity-50 flex items-center justify-center gap-2">
                    <span v-if="form.processing">Agregando...</span>
                    <span v-else class="flex items-center gap-2">
                        <ShoppingBag :size="18"/> Agregar al Carrito
                    </span>
                </button>
            </div>

        </div>
    </div>
</template>