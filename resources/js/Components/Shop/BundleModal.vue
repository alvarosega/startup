<script setup>
import { ref, watch, computed, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { X, ShoppingCart, Plus, Minus, Zap, TrendingUp, AlertCircle } from 'lucide-vue-next';
import axios from 'axios';

const props = defineProps({ 
    show: Boolean, 
    bundleSlug: String 
});

const emit = defineEmits(['close']);

// Estados
const bundle = ref(null);
const localItems = ref([]);
const loading = ref(false);
const bundleQuantity = ref(1);
const animatePrice = ref(false);
let abortController = null;

const resolveUnitPrice = (item) => {
    if (!item.price_tiers || item.price_tiers.length === 0) return item.base_price;
    
    // IMPORTANTE: Ordenamos de mayor a menor cantidad para encontrar el "techo" alcanzado
    const tiers = [...item.price_tiers].sort((a, b) => b.min_quantity - a.min_quantity);
    
    const matchingTier = tiers.find(t => item.quantity >= t.min_quantity);
    
    return matchingTier ? matchingTier.final_price : item.base_price;
};

const getNextTier = (item) => {
    if (!item.price_tiers || item.price_tiers.length === 0) return null;
    
    // Ordenamos de menor a mayor para buscar el siguiente escalón inmediato
    const tiers = [...item.price_tiers].sort((a, b) => a.min_quantity - b.min_quantity);
    
    return tiers.find(t => t.min_quantity > item.quantity);
};

// --- CÁLCULOS DINÁMICOS ---
const totalPrice = computed(() => {
    if (!bundle.value) return "0.00";
    
    // Caso 1: Precio Fijo de Pack
    if (bundle.value.fixed_price) {
        return (bundle.value.fixed_price * bundleQuantity.value).toFixed(2);
    }

    // Caso 2: Suma dinámica de SKUs con sus respectivos Tiers
    const totalContent = localItems.value.reduce((acc, item) => {
        return acc + (resolveUnitPrice(item) * item.quantity);
    }, 0);

    return (totalContent * bundleQuantity.value).toFixed(2);
});

// --- HELPERS ---
const getImageUrl = (path) => {
    if (!path) return '/assets/img/placeholder.png';
    if (path.startsWith('http')) return path;
    return `/storage/${path.replace(/^\/+/, '')}`;
};

const triggerPriceAnimation = () => {
    animatePrice.value = true;
    setTimeout(() => { animatePrice.value = false; }, 300);
};

// --- CONTROLADORES ---
const updateSkuQty = (index, delta) => {
    if (!bundle.value.is_editable) return; // Bloqueo si no es editable
    const newQty = localItems.value[index].quantity + delta;
    if (newQty >= 1) {
        localItems.value[index].quantity = newQty;
        triggerPriceAnimation();
    }
};

const updateBundleQty = (delta) => {
    const newQty = bundleQuantity.value + delta;
    if (newQty >= 1) {
        bundleQuantity.value = newQty;
        triggerPriceAnimation();
    }
};

const handleClose = () => {
    bundle.value = null;
    localItems.value = [];
    bundleQuantity.value = 1;
    if (abortController) abortController.abort();
    emit('close');
};

// --- CARGA DE DATOS ---
watch(() => props.show, async (newVal) => {
    if (newVal && props.bundleSlug) {
        loading.value = true;
        abortController = new AbortController();

        try {
            const res = await axios.get(route('customer.cart.bundle.show', props.bundleSlug), {
                signal: abortController.signal
            });
            
            bundle.value = res.data;
            localItems.value = res.data.skus.map(sku => ({
                sku_id: sku.id,
                name: sku.name,
                image: sku.image,
                quantity: sku.default_qty || 1,
                base_price: sku.base_price,
                price_tiers: sku.price_tiers || []
            }));
            console.log("TIERS RECIBIDOS:", JSON.parse(JSON.stringify(localItems.value[0].price_tiers)));
        } catch (error) {
            if (!axios.isCancel(error)) handleClose();
        } finally {
            loading.value = false;
        }
    }
});

onUnmounted(() => {
    if (abortController) abortController.abort();
});
const submit = () => {
    router.post(route('customer.cart.bundle.add'), {
        bundle_id: bundle.value.id,
        quantity: bundleQuantity.value,
        // Este campo es vital para el backend si el pack es editable
        custom_items: Object.fromEntries(localItems.value.map(i => [i.sku_id, i.quantity])),
        guest_client_uuid: localStorage.getItem('guest_client_uuid')
    }, {
        onSuccess: () => handleClose(),
        preserveScroll: true
    });
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/80 backdrop-blur-md">
        <div class="bg-card w-full max-w-md rounded-[32px] overflow-hidden border border-white/10 shadow-2xl flex flex-col max-h-[85vh]">
            
            <div v-if="loading" class="flex flex-col h-full animate-pulse">
                <div class="h-40 bg-white/5 shrink-0"></div>
                <div class="p-6 shrink-0">
                    <div class="h-8 bg-white/10 rounded-md w-3/4 mb-2"></div>
                    <div class="h-4 bg-white/5 rounded-md w-full"></div>
                </div>
                <div class="flex-1 overflow-hidden p-6 pt-0 space-y-4">
                    <div v-for="i in 3" :key="i" class="h-16 bg-white/5 rounded-xl"></div>
                </div>
                <div class="p-6 shrink-0 border-t border-white/5">
                    <div class="h-12 bg-white/10 rounded-2xl w-full"></div>
                </div>
            </div>
            
            <template v-else-if="bundle">
                <div class="shrink-0 relative">
                    <button @click="handleClose" class="absolute top-4 right-4 z-20 bg-black/40 hover:bg-black/60 transition-colors text-white rounded-full p-2">
                        <X :size="20" />
                    </button>
                    
                    <div class="h-40 relative overflow-hidden flex items-center justify-center p-6">
                        <img :src="getImageUrl(bundle.image_url)" class="absolute inset-0 w-full h-full object-cover">
                        <div class="absolute inset-0 bg-black/70 backdrop-blur-[2px]"></div>

                        <div class="relative z-10 flex items-center justify-center -space-x-4">
                            <template v-for="(item, index) in localItems.slice(0, 4)" :key="item.sku_id">
                                <img 
                                    :src="getImageUrl(item.image)" 
                                    class="w-16 h-16 rounded-full ring-2 ring-black object-cover bg-white/10 shadow-lg border border-white/10"
                                >
                            </template>
                            <div v-if="localItems.length > 4" class="w-16 h-16 rounded-full ring-2 ring-black bg-black/80 backdrop-blur-md flex items-center justify-center shadow-lg z-10 border border-white/10">
                                <span class="text-sm font-black text-white">+{{ localItems.length - 4 }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 pt-6 pb-2">
                        <div class="flex flex-col gap-2">
                            <div class="flex justify-between items-start gap-4">
                                <h3 class="text-2xl font-black uppercase italic leading-tight text-primary">{{ bundle.name }}</h3>
                                <div class="flex flex-col gap-1 items-end shrink-0">
                                    <span v-if="bundle.is_editable" class="px-2 py-0.5 bg-primary/10 text-primary border border-primary/20 text-[9px] font-black uppercase tracking-widest rounded-sm">Modificable</span>
                                    <span v-else class="px-2 py-0.5 bg-red-500/10 text-red-500 border border-red-500/20 text-[9px] font-black uppercase tracking-widest rounded-sm flex items-center gap-1"><AlertCircle :size="10" /> Cerrado</span>
                                    <span v-if="bundle.fixed_price" class="px-2 py-0.5 bg-white/5 text-muted-foreground border border-white/10 text-[9px] font-black uppercase tracking-widest rounded-sm">Precio Fijo</span>
                                </div>
                            </div>
                            <p class="text-sm text-muted-foreground line-clamp-2">{{ bundle.description }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto px-6 py-4 space-y-3 custom-scrollbar">
                    <div v-for="(item, index) in localItems" :key="item.sku_id" 
                         class="flex flex-col bg-white/5 p-3 rounded-xl border border-white/5 transition-colors hover:bg-white/10">
                        
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold line-clamp-1 leading-tight uppercase">{{ item.name }}</p>
                                <p class="text-xs text-primary/70 font-mono mt-0.5">
                                    Bs {{ resolveUnitPrice(item).toFixed(2) }}/u
                                </p>
                            </div>

                            <div v-if="bundle.is_editable" class="flex items-center bg-black/40 rounded-lg p-1 shrink-0 border border-white/5">
                                <button 
                                    @click="updateSkuQty(index, -1)" 
                                    :disabled="item.quantity <= 1"
                                    class="w-8 h-8 flex items-center justify-center hover:text-primary transition-colors disabled:opacity-30"
                                >
                                    <Minus :size="14" />
                                </button>
                                
                                <span class="w-6 text-center font-mono font-bold text-sm">{{ item.quantity }}</span>

                                <button 
                                    @click="updateSkuQty(index, 1)" 
                                    class="w-8 h-8 flex items-center justify-center hover:text-primary transition-colors disabled:opacity-30"
                                >
                                    <Plus :size="14" />
                                </button>
                            </div>
                            <div v-else class="h-10 px-3 flex items-center justify-center bg-black/40 rounded-lg border border-white/5 font-mono text-sm font-bold text-muted-foreground shrink-0">
                                x{{ item.quantity }}
                            </div>
                        </div>

                        <div v-if="bundle.is_editable && getNextTier(item)" class="mt-2 flex items-start gap-1.5 text-[10px] font-bold text-primary/80 bg-primary/5 py-1.5 px-2 rounded-lg border border-primary/20 leading-none">
                            <TrendingUp :size="12" class="shrink-0" />
                            <span>AÑADE {{ getNextTier(item).min_quantity - item.quantity }} MÁS PARA BAJAR A Bs {{ getNextTier(item).final_price.toFixed(2) }}</span>
                        </div>
                    </div>
                </div>

                <div class="shrink-0 p-6 border-t border-white/10 bg-card/95 backdrop-blur-sm flex flex-col gap-6">
                    <div class="flex items-end justify-between">
                        <div>
                            <p class="text-[10px] uppercase font-black text-muted-foreground mb-1">Cant. Packs</p>
                            <div class="flex items-center bg-muted rounded-xl p-1 border border-white/10">
                                <button @click="updateBundleQty(-1)" class="w-10 h-10 flex items-center justify-center hover:text-primary transition-colors disabled:opacity-30" :disabled="bundleQuantity <= 1">
                                    <Minus :size="16" />
                                </button>
                                <span class="w-10 text-center font-mono font-bold text-lg">{{ bundleQuantity }}</span>
                                <button @click="updateBundleQty(1)" class="w-10 h-10 flex items-center justify-center hover:text-primary transition-colors">
                                    <Plus :size="16" />
                                </button>
                            </div>
                        </div>

                        <div class="text-right">
                            <p class="text-[10px] uppercase font-black text-primary mb-1">Total Proyectado</p>
                            <p class="text-3xl font-black font-mono transition-transform duration-200" :class="{ 'scale-110 text-primary': animatePrice }">
                                Bs {{ totalPrice }}
                            </p>
                        </div>
                    </div>

                    <button @click="submit" class="w-full py-4 bg-primary hover:bg-primary/90 text-black font-black uppercase rounded-2xl flex items-center justify-center gap-3 shadow-[0_0_20px_rgba(var(--primary),0.3)] transition-all active:scale-[0.98]">
                        <ShoppingCart :size="20" /> Añadir al pedido
                    </button>
                </div>
            </template>
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.2);
}
</style>