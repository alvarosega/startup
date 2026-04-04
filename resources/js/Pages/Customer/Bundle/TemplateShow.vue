<script setup>
import { computed, ref } from 'vue';
import { router, Head } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import { ShoppingCart, Plus, Minus, Loader2, CheckCircle2, Zap } from 'lucide-vue-next';
import EditableBundleCarousel from '@/Components/Customer/Bundle/EditableBundleCarousel.vue';

const props = defineProps({
    bundle: Object,
    templateBundles: Object, // Ahora trae todos, incluyendo el actual
    currentCart: Object
});

const isBulkLoading = ref(false);
const processingItems = ref(new Set());

const bundleData = computed(() => props.bundle?.data || props.bundle || {});

// REORDENAMIENTO DEL CARRUSEL: Forzamos el pack actual a la primera posición
const carouselBundles = computed(() => {
    const all = props.templateBundles?.data || [];
    const currentId = bundleData.value.id;
    
    if (!currentId) return all;
    
    const current = all.find(b => b.id === currentId);
    const others = all.filter(b => b.id !== currentId);
    
    return current ? [current, ...others] : all;
});

const itemsWithState = computed(() => {
    const items = bundleData.value.items || [];
    return items.map(item => {
        const cartQty = props.currentCart[item.sku_id]?.qty || 0;
        return {
            ...item,
            in_cart: cartQty,
            is_exhausted: item.stock_available <= 0,
            has_suggestion_met: cartQty >= item.quantity
        };
    });
});

const handleAddFullPack = () => {
    if (isBulkLoading.value || !bundleData.value.id) return;
    isBulkLoading.value = true;
    router.post(route('customer.cart.add-template'), { 
        bundle_id: bundleData.value.id 
    }, {
        preserveScroll: true,
        onFinish: () => isBulkLoading.value = false
    });
};

const updateItem = (skuId, currentQty, delta) => {
    const newQty = Math.max(0, currentQty + delta);
    if (processingItems.value.has(skuId)) return;
    
    processingItems.value.add(skuId);
    router.post(route('customer.cart.upsert'), {
        target_id: skuId, target_type: 'sku', quantity: newQty, mode: 'set'
    }, {
        preserveScroll: true,
        only: ['currentCart', 'cart', 'flash'],
        onFinish: () => processingItems.value.delete(skuId)
    });
};
</script>

<template>
    <ShopLayout>
        <Head :title="bundleData.name || 'Configurar Pack'" /> 

        <div v-if="carouselBundles.length > 0" class="w-full pt-8 pb-6">
            <div class="max-w-7xl mx-auto px-6 md:px-12 mb-4">
                <h2 class="text-[10px] font-black uppercase tracking-[0.4em] text-foreground/40 flex items-center gap-2">
                    <Zap :size="12" class="text-primary" /> Menú de Packs
                </h2>
            </div>
            <EditableBundleCarousel :bundles="carouselBundles" :current-id="bundleData.id" />
        </div>

        <main class="max-w-6xl mx-auto px-6 pb-24">
            
            <div class="flex flex-col md:flex-row items-start md:items-end justify-between gap-6 mb-12 border-b border-white/10 pb-8">
                <div>
                    <h1 class="text-4xl md:text-6xl font-black uppercase italic tracking-tighter leading-none mb-3 text-foreground">
                        {{ bundleData.name }}
                    </h1>
                    <p class="text-foreground/50 text-sm md:text-base font-medium max-w-2xl">
                        {{ bundleData.description }}
                    </p>
                </div>

                <button @click="handleAddFullPack" 
                        :disabled="isBulkLoading"
                        class="h-16 px-8 bg-primary text-white rounded-2xl font-black uppercase tracking-widest text-xs flex items-center justify-center gap-3 hover:bg-primary/90 hover:scale-105 active:scale-95 transition-all w-full md:w-auto shadow-xl shadow-primary/20 disabled:opacity-50 shrink-0">
                    <Loader2 v-if="isBulkLoading" class="animate-spin" :size="20" />
                    <ShoppingCart v-else :size="20" stroke-width="3"/>
                    {{ isBulkLoading ? 'Procesando...' : 'Llevar Todo' }}
                </button>
            </div>

            <div class="grid md:grid-cols-2 gap-6">
                <div v-for="item in itemsWithState" :key="item.sku_id" 
                     class="group relative flex items-center gap-6 p-6 rounded-[3rem] border transition-all duration-500 bg-white/5 backdrop-blur-md"
                     :class="item.is_exhausted ? 'opacity-40 grayscale border-transparent' : 'border-white/5 hover:border-primary/40 hover:bg-white/10 hover:shadow-xl'">
                    
                    <img :src="item.image" class="w-24 h-24 object-contain group-hover:scale-110 transition-transform duration-500 drop-shadow-lg">
                    
                    <div class="flex-1 min-w-0">
                        <h4 class="font-black uppercase text-sm truncate text-foreground/90 group-hover:text-foreground transition-colors">{{ item.name }}</h4>
                        <p class="text-[11px] font-bold text-primary mb-5">Bs. {{ item.final_price.toFixed(2) }} <span class="text-foreground/40 font-sans italic tracking-normal">c/u</span></p>
                        
                        <div class="flex items-center gap-5">
                            <div class="flex items-center bg-background border border-white/10 rounded-2xl p-1.5 shadow-inner">
                                <button @click="updateItem(item.sku_id, item.in_cart, -1)" 
                                        :disabled="processingItems.has(item.sku_id) || item.in_cart <= 0" 
                                        class="w-8 h-8 flex items-center justify-center rounded-xl hover:bg-white/10 transition-colors disabled:opacity-20 text-foreground">
                                    <Minus :size="16" stroke-width="3"/>
                                </button>
                                
                                <div class="w-10 text-center flex flex-col justify-center">
                                    <Loader2 v-if="processingItems.has(item.sku_id)" class="animate-spin text-primary mx-auto" :size="16"/>
                                    <span v-else class="font-mono font-black text-base">{{ item.in_cart }}</span>
                                </div>
                                
                                <button @click="updateItem(item.sku_id, item.in_cart, 1)" 
                                        :disabled="processingItems.has(item.sku_id) || item.in_cart >= item.stock_available" 
                                        class="w-8 h-8 flex items-center justify-center rounded-xl hover:bg-white/10 transition-colors disabled:opacity-20 text-foreground">
                                    <Plus :size="16" stroke-width="3"/>
                                </button>
                            </div>
                            
                            <div class="flex flex-col">
                                <span class="text-[9px] font-black uppercase tracking-widest text-foreground/40">
                                    {{ item.is_exhausted ? 'Sin stock' : `Disponibles: ${item.stock_available}` }}
                                </span>
                                <span v-if="item.quantity > 0" class="text-[9px] font-bold text-accent uppercase italic mt-0.5">
                                    Sugerencia: {{ item.quantity }}u
                                </span>
                            </div>
                        </div>
                    </div>

                    <div v-if="item.has_suggestion_met" class="absolute top-6 right-6">
                        <CheckCircle2 :size="20" class="text-emerald-500 drop-shadow-md" />
                    </div>
                </div>
            </div>
        </main>
    </ShopLayout>
</template>