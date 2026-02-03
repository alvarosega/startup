<script setup>
import { ref, computed, watch } from 'vue';
import axios from 'axios';
import { useForm } from '@inertiajs/vue3';
import { X, ShoppingBag, Plus, Minus, AlertTriangle, Loader2, Package } from 'lucide-vue-next';

const props = defineProps({
    show: Boolean,
    bundleSlug: String,
});

const emit = defineEmits(['close']);

// Estado
const loading = ref(false);
const bundle = ref(null);
const items = ref([]); 
const multiplier = ref(1);
const stockWarnings = ref([]);

// Cargar datos al abrir
watch(() => props.show, async (newVal) => {
    if (newVal && props.bundleSlug) {
        loading.value = true;
        stockWarnings.value = [];
        multiplier.value = 1;
        items.value = [];
        bundle.value = null;
        
        try {
            // Usamos tu controlador existente BundleController@show
            const response = await axios.get(route('shop.bundle.show', props.bundleSlug));
            bundle.value = response.data.bundle;
            
            // Lógica de Stock y Auto-ajuste
            items.value = response.data.items.map(item => {
                let initialQty = item.default_quantity;
                if (initialQty > item.max_stock) {
                    stockWarnings.value.push(`${item.name}: Ajustado a ${item.max_stock} (Stock máx).`);
                    initialQty = item.max_stock;
                }
                return {
                    ...item,
                    base_quantity: initialQty,
                    quantity: initialQty,
                };
            });
        } catch (error) {
            console.error("Error cargando bundle", error);
            emit('close');
        } finally {
            loading.value = false;
        }
    }
});

// Watcher Multiplicador
watch(multiplier, (val) => {
    if (!items.value.length) return;
    items.value.forEach(item => {
        let newQty = item.base_quantity * val;
        if (newQty > item.max_stock) newQty = item.max_stock;
        item.quantity = newQty;
    });
});

const updateItemQty = (item, newQty) => {
    if (newQty < 0 || newQty > item.max_stock) return;
    item.quantity = newQty;
};

const totalPrice = computed(() => {
    return items.value.reduce((acc, item) => acc + (item.quantity * item.unit_price), 0);
});

// Formulario Inertia para enviar al carrito
const form = useForm({ items: [] });

const addToCart = () => {
    const payload = items.value
        .filter(i => i.quantity > 0)
        .map(i => ({ sku_id: i.sku_id, quantity: i.quantity }));

    if (payload.length === 0) return;

    form.items = payload;
    // Asumimos que tienes una ruta para carga masiva
    form.post(route('cart.bulk'), {
        preserveScroll: true,
        onSuccess: () => emit('close'),
    });
};
</script>

<template>
    <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="opacity-0 translate-y-10 scale-95"
        enter-to-class="opacity-100 translate-y-0 scale-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100 translate-y-0 scale-100"
        leave-to-class="opacity-0 translate-y-10 scale-95"
    >
        <div v-if="show" class="fixed inset-0 z-[100] flex items-center justify-center p-4" role="dialog">
            
            <div class="absolute inset-0 bg-black/80 backdrop-blur-md" @click="emit('close')"></div>

            <div class="relative w-full max-w-md bg-slate-900 border border-white/10 rounded-3xl shadow-2xl overflow-hidden flex flex-col max-h-[85vh]">
                
                <div class="px-6 py-4 border-b border-white/5 bg-slate-800/50 flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-emerald-500/20 flex items-center justify-center text-emerald-400">
                            <Package :size="20" />
                        </div>
                        <div>
                            <h2 class="font-black text-white text-lg leading-none">
                                {{ loading ? 'Cargando...' : bundle?.name }}
                            </h2>
                            <p class="text-[10px] text-slate-400 uppercase tracking-widest mt-1">Configura tu pack</p>
                        </div>
                    </div>
                    <button @click="emit('close')" class="p-2 hover:bg-white/10 rounded-full text-white transition"><X :size="20"/></button>
                </div>

                <div class="flex-1 overflow-y-auto p-6 relative custom-scrollbar">
                    
                    <div v-if="loading" class="absolute inset-0 flex items-center justify-center z-10 bg-slate-900">
                        <Loader2 class="animate-spin text-emerald-500" :size="32"/>
                    </div>

                    <div v-if="stockWarnings.length > 0" class="mb-4 bg-yellow-500/10 p-3 rounded-xl border border-yellow-500/20">
                        <div class="flex gap-2 items-start">
                            <AlertTriangle class="text-yellow-500 shrink-0" :size="16" />
                            <ul class="text-[10px] text-yellow-200 list-disc pl-4 space-y-1">
                                <li v-for="(warn, i) in stockWarnings" :key="i">{{ warn }}</li>
                            </ul>
                        </div>
                    </div>

                    <div class="mb-6 flex items-center justify-between bg-white/5 p-4 rounded-2xl border border-white/5">
                        <span class="font-bold text-slate-300 text-sm">Cantidad de Packs</span>
                        <div class="flex items-center gap-4">
                            <button @click="multiplier = Math.max(1, multiplier - 1)" class="w-8 h-8 flex items-center justify-center bg-slate-800 rounded-full text-white hover:bg-slate-700 transition">-</button>
                            <span class="font-black text-xl text-white w-6 text-center">{{ multiplier }}</span>
                            <button @click="multiplier++" class="w-8 h-8 flex items-center justify-center bg-emerald-500 rounded-full text-white hover:bg-emerald-400 transition shadow-lg shadow-emerald-500/20">+</button>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div v-for="item in items" :key="item.sku_id" class="flex gap-3 items-center bg-slate-800/50 p-2 rounded-xl border border-white/5">
                            <div class="w-12 h-12 bg-white rounded-lg shrink-0 flex items-center justify-center overflow-hidden">
                                <img :src="item.image" class="w-full h-full object-contain">
                            </div>
                            
                            <div class="flex-1 min-w-0">
                                <p class="font-bold text-xs text-white truncate">{{ item.name }}</p>
                                <p class="text-[10px] text-slate-500">Stock máx: {{ item.max_stock }}</p>
                            </div>

                            <div class="flex flex-col items-end gap-1">
                                <div class="flex items-center bg-slate-900 rounded-lg border border-white/10">
                                    <button @click="updateItemQty(item, item.quantity - 1)" :disabled="item.quantity <= 0" class="px-2 py-1 text-slate-400 hover:text-white disabled:opacity-30"><Minus :size="10"/></button>
                                    <span class="text-xs font-bold text-white w-5 text-center">{{ item.quantity }}</span>
                                    <button @click="updateItemQty(item, item.quantity + 1)" :disabled="item.quantity >= item.max_stock" class="px-2 py-1 text-slate-400 hover:text-white disabled:opacity-30"><Plus :size="10"/></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-5 border-t border-white/5 bg-slate-800/80 backdrop-blur">
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-slate-400 text-xs uppercase tracking-wider font-bold">Total Estimado</span>
                        <span class="text-xl font-black text-emerald-400">Bs {{ totalPrice.toFixed(2) }}</span>
                    </div>
                    
                    <button @click="addToCart" :disabled="form.processing || items.every(i => i.quantity === 0)"
                        class="w-full bg-emerald-500 text-white font-black py-4 rounded-xl shadow-lg shadow-emerald-500/20 hover:bg-emerald-400 transition active:scale-95 disabled:opacity-50 disabled:grayscale flex items-center justify-center gap-2 text-sm uppercase tracking-widest">
                        <span v-if="form.processing">Procesando...</span>
                        <span v-else class="flex items-center gap-2">
                            <ShoppingBag :size="18"/> Agregar al Carrito
                        </span>
                    </button>
                </div>

            </div>
        </div>
    </Transition>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: rgba(255,255,255,0.05); }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 4px; }
</style>