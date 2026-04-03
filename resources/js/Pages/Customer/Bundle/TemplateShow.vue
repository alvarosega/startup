<script setup>
import { computed, ref } from 'vue';
import { router, Head } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import { ShoppingCart, Plus, Minus, Sparkles, Loader2, Package, CheckCircle2, Zap } from 'lucide-vue-next';
import EditableBundleCarousel from '@/Components/Customer/Bundle/EditableBundleCarousel.vue';

const props = defineProps({
    bundle: Object,
    otherBundles: Object, // Traído por el controlador (BundleResource collection)
    currentCart: Object
});

const isBulkLoading = ref(false);
const processingItems = ref(new Set());

const bundleData = computed(() => props.bundle?.data || props.bundle || {});

// 2. REFACTORIZAR COMPUTED PROPERTIES CON GUARDIA
const itemsWithState = computed(() => {
    // Si no hay items aún, retornamos vacío para evitar el crash del reduce/map
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

const packTotal = computed(() => {
    const items = bundleData.value.items || [];
    return items.reduce((sum, i) => sum + (i.final_price * i.quantity), 0);
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
        only: ['currentCart', 'flash'],
        onFinish: () => processingItems.value.delete(skuId)
    });
};
</script>

<template>
    <ShopLayout>
        <Head :title="bundle.name" />

        <div v-if="otherBundles.data?.length > 0" class="w-full bg-background pt-8 pb-4 border-b border-white/5">
            <div class="px-12 mb-4">
                <h2 class="text-[9px] font-black uppercase tracking-[0.4em] text-foreground/30 flex items-center gap-2">
                    <Zap :size="10" /> Explorar otros packs editables
                </h2>
            </div>
            <EditableBundleCarousel :bundles="otherBundles.data" />
        </div>

        <div class="max-w-6xl mx-auto px-6 py-16">
            <div class="flex flex-col lg:flex-row gap-12 items-start mb-20">
                <div class="w-full lg:w-96 relative">
                    <img :src="bundle.image_url" class="w-full aspect-square object-cover rounded-[3.5rem] shadow-2xl border border-white/5">
                    <div class="absolute -bottom-4 -right-4 bg-primary text-white p-5 rounded-3xl shadow-xl">
                        <Sparkles :size="24" fill="currentColor"/>
                    </div>
                </div>

                <div class="flex-1 pt-6">
                    <h1 class="text-6xl font-black uppercase italic tracking-tighter leading-[0.9] mb-6">{{ bundle.name }}</h1>
                    <p class="text-foreground/50 text-xl font-medium mb-10 max-w-2xl">{{ bundle.description }}</p>
                    
                    <div class="inline-flex flex-col md:flex-row items-center gap-6 bg-card border border-white/5 p-6 rounded-[2.5rem] shadow-xl w-full md:w-auto">
                        <div class="text-center md:text-right px-4">
                            <p class="text-[10px] font-bold text-foreground/30 uppercase tracking-widest mb-1">Total de este pack</p>
                            <p class="text-4xl font-mono font-black text-primary">Bs. {{ packTotal.toFixed(2) }}</p>
                        </div>
                        <button @click="handleAddFullPack" :disabled="isBulkLoading"
                                class="h-16 px-10 bg-primary text-white rounded-2xl font-black uppercase text-sm flex items-center gap-3 hover:scale-105 active:scale-95 transition-all w-full md:w-auto justify-center">
                            <Loader2 v-if="isBulkLoading" class="animate-spin" />
                            <ShoppingCart v-else :size="20" stroke-width="3"/>
                            Llevar Selección Completa
                        </button>
                    </div>
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-6">
                <div v-for="item in itemsWithState" :key="item.sku_id" 
                     class="group relative flex items-center gap-6 p-6 rounded-[3rem] border bg-white/5 backdrop-blur-md transition-all duration-500"
                     :class="item.is_exhausted ? 'opacity-40 grayscale border-transparent' : 'border-white/5 hover:border-primary/40 hover:bg-white/10'">
                    
                    <img :src="item.image" class="w-24 h-24 object-contain group-hover:scale-110 transition-transform duration-500">
                    
                    <div class="flex-1 min-w-0">
                        <h4 class="font-black uppercase text-sm truncate text-foreground/80">{{ item.name }}</h4>
                        <p class="text-[11px] font-bold text-primary mb-4">Bs. {{ item.final_price.toFixed(2) }} c/u</p>
                        
                        <div class="flex items-center gap-4">
                            <div class="flex items-center bg-background/50 border border-white/10 rounded-2xl p-1 shadow-inner">
                                <button @click="updateItem(item.sku_id, item.in_cart, -1)" :disabled="processingItems.has(item.sku_id) || item.in_cart <= 0" class="p-2 hover:text-primary transition-colors"><Minus :size="14"/></button>
                                <div class="w-8 text-center font-mono font-black text-sm">
                                    <Loader2 v-if="processingItems.has(item.sku_id)" class="animate-spin text-primary" :size="14"/>
                                    <span v-else>{{ item.in_cart }}</span>
                                </div>
                                <button @click="updateItem(item.sku_id, item.in_cart, 1)" :disabled="processingItems.has(item.sku_id) || item.in_cart >= item.stock_available" class="p-2 hover:text-primary transition-colors"><Plus :size="14"/></button>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-[9px] font-black uppercase text-foreground/30">{{ item.is_exhausted ? 'Agotado' : `Disponibles: ${item.stock_available}` }}</span>
                                <span v-if="item.quantity > 0" class="text-[8px] font-bold text-primary uppercase italic">Referencia: {{ item.quantity }}u</span>
                            </div>
                        </div>
                    </div>
                    <div v-if="item.has_suggestion_met" class="absolute top-8 right-8">
                        <CheckCircle2 :size="18" class="text-emerald-500 shadow-sm" />
                    </div>
                </div>
            </div>
        </div>
    </ShopLayout>
</template>
