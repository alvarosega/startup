<script setup>
import { ref, computed, watch } from 'vue';
import axios from 'axios';
import { useForm } from '@inertiajs/vue3';
import { X, ShoppingBag, Plus, Minus, AlertTriangle, Loader2, Package, Layers } from 'lucide-vue-next';

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
            const response = await axios.get(route('shop.bundle.show', props.bundleSlug));
            bundle.value = response.data.bundle;
            
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

const form = useForm({ items: [] });

const addToCart = () => {
    const payload = items.value
        .filter(i => i.quantity > 0)
        .map(i => ({ sku_id: i.sku_id, quantity: i.quantity }));

    if (payload.length === 0) return;

    form.items = payload;
    form.post(route('cart.bulk'), {
        preserveScroll: true,
        onSuccess: () => emit('close'),
    });
};
</script>

<template>
    <Transition
        enter-active-class="transition duration-300 cubic-bezier(0.16, 1, 0.3, 1)"
        enter-from-class="opacity-0 scale-95 translate-y-4"
        enter-to-class="opacity-100 scale-100 translate-y-0"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100 scale-100 translate-y-0"
        leave-to-class="opacity-0 scale-95 translate-y-4"
    >
        <div v-if="show" class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6" role="dialog">
            
            <div class="absolute inset-0 bg-background/80 backdrop-blur-xl transition-opacity" @click="emit('close')"></div>

            <div class="relative w-full max-w-lg bg-card/90 dark:bg-[#0B1221]/90 backdrop-blur-2xl border border-border/10 rounded-[32px] shadow-2xl overflow-hidden flex flex-col max-h-[90vh] ring-1 ring-white/5">
                
                <div class="px-6 py-5 border-b border-border/10 flex justify-between items-start bg-gradient-to-r from-primary/5 to-transparent">
                    <div class="flex gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-primary/10 border border-primary/20 flex items-center justify-center text-primary shadow-[0_0_15px_rgba(0,240,255,0.15)]">
                            <Layers :size="24" />
                        </div>
                        <div>
                            <span class="text-[10px] font-black text-primary uppercase tracking-widest mb-1 block">Configuración de Pack</span>
                            <h2 class="font-display font-black text-foreground text-2xl leading-none tracking-tight">
                                {{ loading ? 'Cargando...' : bundle?.name }}
                            </h2>
                        </div>
                    </div>
                    <button @click="emit('close')" class="p-2 hover:bg-white/10 rounded-full text-muted-foreground hover:text-foreground transition active:scale-90"><X :size="24"/></button>
                </div>

                <div class="flex-1 overflow-y-auto p-6 relative custom-scrollbar bg-gradient-to-b from-transparent to-black/5">
                    
                    <div v-if="loading" class="absolute inset-0 flex items-center justify-center z-10 bg-background/50 backdrop-blur-sm">
                        <Loader2 class="animate-spin text-primary" :size="40"/>
                    </div>

                    <div v-if="stockWarnings.length > 0" class="mb-4 bg-amber-100/80 dark:bg-yellow-500/10 border border-amber-200 dark:border-yellow-500/20 p-3 rounded-xl flex items-center gap-3 shadow-sm">
                        <AlertTriangle class="text-amber-600 dark:text-yellow-500 shrink-0" :size="18" />
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold text-amber-800 dark:text-yellow-500 leading-none mb-0.5">Stock Ajustado</p>
                            <p class="text-[10px] text-amber-700/80 dark:text-yellow-200/70 truncate">
                                {{ stockWarnings.length }} productos ajustados a su disponibilidad máxima.
                            </p>
                        </div>
                    </div>

                    <div class="mb-8 bg-muted/30 p-1 rounded-2xl border border-border/10 flex items-center justify-between shadow-inner">
                        <div class="px-4 py-2">
                            <span class="block text-[10px] font-bold text-muted-foreground uppercase tracking-wider">Cantidad de Packs</span>
                            <span class="text-xs text-foreground/60">Multiplica todos los items</span>
                        </div>
                        <div class="flex items-center gap-1 bg-card rounded-xl p-1 shadow-sm border border-border/5">
                            <button @click="multiplier = Math.max(1, multiplier - 1)" class="w-10 h-10 flex items-center justify-center rounded-lg hover:bg-muted text-foreground transition active:scale-90"><Minus :size="18"/></button>
                            <div class="w-12 text-center font-black text-xl text-primary font-mono">{{ multiplier }}</div>
                            <button @click="multiplier++" class="w-10 h-10 flex items-center justify-center bg-primary text-primary-foreground rounded-lg hover:brightness-110 transition active:scale-90 shadow-lg shadow-primary/20"><Plus :size="18"/></button>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <h3 class="text-xs font-black text-muted-foreground uppercase tracking-widest pl-1">Contenido del Pack</h3>
                        
                        <div class="grid grid-cols-1 gap-3">
                            <div v-for="item in items" :key="item.sku_id" 
                                 class="flex gap-4 items-center bg-card hover:bg-muted/30 p-3 rounded-2xl border border-border/10 transition-all group">
                                
                                <div class="w-16 h-16 bg-white rounded-xl shrink-0 flex items-center justify-center overflow-hidden border border-border/10 shadow-sm group-hover:scale-105 transition-transform duration-300">
                                    <img :src="item.image" class="w-full h-full object-contain p-2">
                                </div>
                                
                                <div class="flex-1 min-w-0 py-1 flex flex-col justify-center">
                                    <p class="font-bold text-sm text-foreground truncate leading-tight">{{ item.name }}</p>
                                </div>

                                <div class="flex flex-col items-end gap-1">
                                    <div class="flex items-center bg-muted/50 rounded-lg border border-border/10 p-0.5">
                                        <button @click="updateItemQty(item, item.quantity - 1)" :disabled="item.quantity <= 0" class="w-7 h-7 flex items-center justify-center text-muted-foreground hover:text-foreground hover:bg-white/10 rounded disabled:opacity-30 transition"><Minus :size="12"/></button>
                                        <span class="text-xs font-black text-foreground w-6 text-center font-mono">{{ item.quantity }}</span>
                                        <button @click="updateItemQty(item, item.quantity + 1)" :disabled="item.quantity >= item.max_stock" class="w-7 h-7 flex items-center justify-center text-muted-foreground hover:text-foreground hover:bg-white/10 rounded disabled:opacity-30 transition"><Plus :size="12"/></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-6 border-t border-border/10 bg-card/50 backdrop-blur-md">
                    <div class="flex justify-between items-end mb-4 px-1">
                        <div class="flex flex-col">
                            <span class="text-[10px] text-muted-foreground uppercase tracking-wider font-bold mb-0.5">Total Estimado</span>
                            <span class="text-xs text-primary/70 font-mono">{{ items.reduce((a,i) => a + i.quantity, 0) }} productos</span>
                        </div>
                        <span class="text-3xl font-black text-foreground tracking-tight">Bs {{ totalPrice.toFixed(2) }}</span>
                    </div>
                    
                    <button @click="addToCart" :disabled="form.processing || items.every(i => i.quantity === 0)"
                        class="w-full bg-primary hover:bg-primary/90 text-primary-foreground font-black py-4 rounded-xl shadow-[0_0_20px_rgba(0,240,255,0.3)] transition-all active:scale-95 disabled:opacity-50 disabled:grayscale flex items-center justify-center gap-3 text-sm uppercase tracking-widest hover:shadow-[0_0_30px_rgba(0,240,255,0.5)]">
                        <span v-if="form.processing" class="flex items-center gap-2"><Loader2 class="animate-spin" :size="18"/> Procesando...</span>
                        <span v-else class="flex items-center gap-2">
                            <ShoppingBag :size="20"/> Agregar Pack al Pedido
                        </span>
                    </button>
                </div>

            </div>
        </div>
    </Transition>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(156, 163, 175, 0.3); border-radius: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(156, 163, 175, 0.5); }
</style>