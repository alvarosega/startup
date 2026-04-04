<script setup>
import { computed, ref } from 'vue';
import { router, Head } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import { Plus, Minus, Loader2, CheckCircle2, Zap, LayoutGrid } from 'lucide-vue-next';
import EditableBundleCarousel from '@/Components/Customer/Bundle/EditableBundleCarousel.vue';

const props = defineProps({
    bundle: Object,
    templateBundles: Object, 
    currentCart: Object
});

const processingItems = ref(new Set());
const bundleData = computed(() => props.bundle?.data || props.bundle || {});

// REORDENAMIENTO: Pack actual primero
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

        <div v-if="carouselBundles.length > 0" class="w-full pt-0 pb-10">
            <div class="max-w-7xl mx-auto px-6 md:px-12 mb-6">
                <div class="header-standard">
                    <div class="title-block-wrapper">
                        <Zap :size="16" class="text-black dark:text-primary" />
                        <h2 class="text-black dark:text-white">Menú de Packs</h2>
                    </div>
                </div>
            </div>
            <EditableBundleCarousel :bundles="carouselBundles" :current-id="bundleData.id" />
        </div>

        <main class="max-w-7xl mx-auto px-6 pb-32">
            <div class="header-standard mb-10">
                <div class="title-block-wrapper">
                    <LayoutGrid :size="18" class="text-black dark:text-primary" />
                    <h2 class="text-black dark:text-white">Configurando: {{ bundleData.name }}</h2>
                </div>
            </div>
            
            <div class="grid lg:grid-cols-2 gap-6">
                <div v-for="item in itemsWithState" :key="item.sku_id" 
                     class="glass-chassis group relative flex items-center gap-6 p-5 transition-all duration-500"
                     :class="item.is_exhausted ? 'opacity-40 grayscale' : 'hover:border-primary/40 hover:shadow-2xl'">
                    
                    <div class="w-28 h-28 shrink-0 rounded-2xl sku-stage-bg p-3 flex items-center justify-center relative">
                        <img :src="item.image" class="w-full h-full object-contain group-hover:scale-110 transition-transform duration-500 drop-shadow-hardware">
                        
                        <div v-if="item.has_suggestion_met" class="absolute -top-2 -right-2 bg-emerald-500 text-white rounded-full p-1 shadow-lg border-2 border-white dark:border-neutral-900 animate-in zoom-in">
                            <CheckCircle2 :size="14" stroke-width="4" />
                        </div>
                    </div>
                    
                    <div class="flex-1 min-w-0">
                        <h4 class="font-black uppercase text-xs md:text-sm truncate text-black dark:text-white mb-1">
                            {{ item.name }}
                        </h4>
                        
                        <div class="flex items-baseline gap-1 mb-4">
                            <span class="text-[10px] font-black text-primary uppercase">Bs</span>
                            <span class="text-xl font-black text-black dark:text-white font-mono leading-none">
                                {{ item.final_price.toFixed(2) }}
                            </span>
                        </div>
                        
                        <div class="flex items-center gap-4">
                            <div class="flex items-center bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 rounded-xl p-1 shadow-inner">
                                <button @click="updateItem(item.sku_id, item.in_cart, -1)" 
                                        :disabled="processingItems.has(item.sku_id) || item.in_cart <= 0" 
                                        class="w-8 h-8 flex items-center justify-center rounded-lg bg-black dark:bg-white text-white dark:text-black hover:opacity-80 transition-all disabled:opacity-20">
                                    <Minus :size="14" stroke-width="4"/>
                                </button>
                                
                                <div class="w-10 text-center">
                                    <Loader2 v-if="processingItems.has(item.sku_id)" class="animate-spin text-primary mx-auto" :size="14"/>
                                    <span v-else class="font-mono font-black text-sm text-black dark:text-white">{{ item.in_cart }}</span>
                                </div>
                                
                                <button @click="updateItem(item.sku_id, item.in_cart, 1)" 
                                        :disabled="processingItems.has(item.sku_id) || item.in_cart >= item.stock_available" 
                                        class="w-8 h-8 flex items-center justify-center rounded-lg bg-black dark:bg-white text-white dark:text-black hover:opacity-80 transition-all disabled:opacity-20">
                                    <Plus :size="14" stroke-width="4"/>
                                </button>
                            </div>
                            
                            <div class="flex flex-col leading-tight">
                                <span v-if="item.quantity > 0" class="text-[9px] font-black text-primary uppercase tracking-tighter">
                                    Sugerido: {{ item.quantity }}u
                                </span>
                                <span v-if="item.is_exhausted" class="text-[9px] font-bold text-destructive uppercase italic">
                                    Fuera de servicio
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </ShopLayout>
</template>

<style scoped>
.font-mono { font-family: 'JetBrains Mono', monospace; }

/* ENCABEZADO PRISMÁTICO */
.header-standard {
    display: flex;
    align-items: flex-end;
    gap: 1rem;
}

.title-block-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    gap: 0.85rem;
    flex-grow: 1;
    padding-bottom: 8px;
}

.title-block-wrapper h2 {
    font-size: 11px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.4em;
}

.title-block-wrapper::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 1px;
    background: linear-gradient(to right, #ff0000, #ff7f00, #ffff00, #00ff00, #0000ff, #4b0082, #8b00ff);
    opacity: 0.8;
}

/* CHASIS DE CRISTAL (Glass Chassis) */
.glass-chassis {
    background-color: hsl(var(--background) / 0.4);
    backdrop-filter: blur(40px) saturate(200%);
    -webkit-backdrop-filter: blur(40px) saturate(200%);
    border: 1px solid hsl(var(--border) / 0.5);
    border-radius: 2.5rem;
}

.dark .glass-chassis {
    background-color: hsl(var(--background) / 0.6);
}

/* BANDEJA DE HARDWARE (Punto focal independiente) */
.sku-stage-bg {
    background-image: linear-gradient(135deg, hsl(var(--primary-50)) 0%, hsl(var(--primary)) 46%, hsl(var(--accent)) 100%);
    box-shadow: 
        rgba(0, 0, 0, 0.1) 0px -15px 20px 0px inset, 
        rgba(0, 0, 0, 0.05) 0px -30px 25px 0px inset,
        0 15px 25px -10px rgba(0, 0, 0, 0.2); /* Flotación */
    border: 1px solid rgba(255, 255, 255, 0.4);
}

.dark .sku-stage-bg {
    background-image: linear-gradient(135deg, #1a1a1a 0%, hsl(var(--primary)) 46%, #000000 100%);
    box-shadow: 
        rgba(0, 0, 0, 0.4) 0px -15px 20px 0px inset,
        0 15px 25px -10px rgba(0, 0, 0, 0.6);
    border-color: rgba(255, 255, 255, 0.05);
}

.drop-shadow-hardware {
    filter: drop-shadow(0 8px 8px rgba(0,0,0,0.25));
}
</style>