<script setup>
import { ref, onMounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { 
    ChevronLeft, 
    Plus, 
    Minus, 
    CheckCircle2, 
    Loader2, 
    AlertTriangle,
    Info,
    Sparkles
} from 'lucide-vue-next';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import HeroCarousel from '@/Components/Shop/HeroCarousel.vue';

const props = defineProps({
    bundle: { type: Object, required: true },
    bundleBanners: { type: Object, default: () => ({ data: [] }) },
    currentCart: { type: Object, default: () => ({}) }
});

const isSyncing = ref(false);

const goBack = () => router.visit(route('customer.index'));

const getCartItem = (skuId) => props.currentCart[skuId] || { qty: 0, price: null };

onMounted(() => {
    const hasItemsInCart = Object.keys(props.currentCart).length > 0;
    
    if (!hasItemsInCart) {
        isSyncing.value = true;
        router.post(route('customer.cart.upsert'), {
            target_id: props.bundle.data.id,
            target_type: 'bundle',
            quantity: 1, 
            mode: 'set',
            custom_quantities: props.bundle.data.items.reduce((acc, item) => {
                acc[item.id] = item.quantity; 
                return acc;
            }, {})
        }, { 
            preserveScroll: true,
            only: ['cart', 'currentCart'],
            onFinish: () => isSyncing.value = false 
        });
    }
});

const syncItem = (skuId, newQty) => {
    isSyncing.value = true;
    router.post(route('customer.cart.upsert'), {
        target_id: skuId,
        target_type: 'sku',
        quantity: newQty,
        mode: 'set' 
    }, { 
        preserveScroll: true,
        only: ['cart', 'currentCart'], 
        onFinish: () => isSyncing.value = false 
    });
};

const updateQty = (item, delta) => {
    const current = getCartItem(item.id).qty;
    const next = Math.max(0, current + delta);
    
    if (next <= item.stock_available) {
        syncItem(item.id, next);
    }
};
</script>

<template>
    <ShopLayout>
        <Head :title="bundle.data.name" />

        <div class="w-full min-h-screen bg-background pb-40">
            <div class="px-6 py-6 flex items-center justify-between border-b border-border/30 bg-card/50 backdrop-blur-md sticky top-0 z-40">
                <div class="flex items-center gap-6">
                    <button @click="goBack" class="p-4 bg-background border border-border/50 rounded-2xl hover:bg-muted transition-colors active:scale-95">
                        <ChevronLeft :size="20" stroke-width="3" />
                    </button>
                    <div>
                        <h1 class="text-2xl font-black uppercase italic tracking-tighter">{{ bundle.data.name }}</h1>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-black text-primary uppercase tracking-[0.2em]">Shopping Template</span>
                            <span v-if="isSyncing" class="flex items-center gap-1 text-[9px] font-bold text-muted-foreground animate-pulse">
                                <Loader2 :size="10" class="animate-spin" /> Sincronizando con Carrito...
                            </span>
                        </div>
                    </div>
                </div>
                <div class="hidden md:flex flex-col items-end">
                    <span class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest leading-none mb-1 text-right">Sucursal Activa</span>
                    <span class="text-xs font-black uppercase italic">Punto Central</span>
                </div>
            </div>

            <HeroCarousel v-if="bundleBanners.data.length > 0" :banners="bundleBanners.data" />
            <div v-else class="w-full h-64 md:h-80 relative overflow-hidden bg-muted/10 border-b border-border/40">
                <img 
                    :src="bundle.data.image_url" 
                    class="w-full h-full object-cover opacity-60"
                    @error="(e) => e.target.src = '/assets/img/bundle_banner_editable.png'" 
                />
                <div class="absolute inset-0 bg-gradient-to-t from-background via-transparent to-transparent flex items-end p-8 max-w-5xl mx-auto">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="px-4 py-1 bg-background/50 backdrop-blur-md border border-border rounded-full text-[10px] font-black uppercase tracking-widest text-primary flex items-center gap-2">
                            <Sparkles :size="14" /> Pack Editable
                        </span>
                    </div>
                </div>
            </div>

            <div class="px-6 md:px-12 mt-10 max-w-5xl mx-auto space-y-4 relative z-10">
                <div v-for="item in bundle.data.items" :key="item.id" 
                     :class="[
                         'flex items-center gap-6 p-6 rounded-[2.5rem] border transition-all duration-500', 
                         (getCartItem(item.id).qty > 0) 
                            ? 'bg-card border-primary/30 shadow-xl shadow-primary/5' 
                            : 'bg-muted/10 border-border/50 opacity-60 grayscale'
                     ]">
                    
                    <div class="relative w-24 h-24 bg-muted/20 rounded-3xl p-4 flex-shrink-0 transition-transform duration-500"
                         :class="{'scale-110': getCartItem(item.id).qty > 0}">
                        <img :src="item.image" class="w-full h-full object-contain" :alt="item.name" @error="(e) => e.target.src = '/assets/img/sku_placeholder.png'" />
                        <div v-if="getCartItem(item.id).qty > 0" 
                             class="absolute -top-2 -right-2 bg-primary text-white p-1.5 rounded-full shadow-lg animate-in zoom-in">
                            <CheckCircle2 :size="14" stroke-width="3" />
                        </div>
                    </div>

                    <div class="flex-1 min-w-0">
                        <h3 class="font-black text-base uppercase tracking-tight truncate leading-none mb-2">
                            {{ item.name }}
                        </h3>
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="text-xl font-black tracking-tighter italic transition-colors"
                                  :class="{'text-primary': getCartItem(item.id).price && getCartItem(item.id).price < item.final_price}">
                                Bs. {{ Number(getCartItem(item.id).price || item.final_price).toFixed(2) }}
                            </span>
                            
                            <div v-if="getCartItem(item.id).price && getCartItem(item.id).price < item.final_price" 
                                 class="bg-primary/10 text-primary px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest border border-primary/20 animate-pulse">
                                Tier Activo
                            </div>
                            <span v-else class="text-[10px] text-muted-foreground font-bold uppercase tracking-widest">
                                Unidad
                            </span>
                        </div>
                        
                        <div v-if="item.stock_available <= 5 && item.stock_available > 0" class="mt-2 text-[9px] font-bold text-amber-500 uppercase flex items-center gap-1">
                            <AlertTriangle :size="10" /> Solo {{ item.stock_available }} disponibles
                        </div>
                    </div>

                    <div class="flex items-center bg-background rounded-[1.8rem] border border-border p-2 gap-2 shadow-inner">
                        <button @click="updateQty(item, -1)" 
                                :disabled="isSyncing || !getCartItem(item.id).qty"
                                class="w-12 h-12 rounded-2xl hover:bg-muted transition-all flex items-center justify-center disabled:opacity-20 active:scale-90">
                            <Minus :size="20" />
                        </button>
                        
                        <div class="w-10 text-center flex flex-col items-center select-none">
                            <span class="text-sm font-black leading-none">{{ getCartItem(item.id).qty }}</span>
                            <span class="text-[7px] font-black text-muted-foreground uppercase tracking-widest">Pack</span>
                        </div>

                        <button @click="updateQty(item, 1)" 
                                :disabled="isSyncing || (getCartItem(item.id).qty >= item.stock_available)"
                                class="w-12 h-12 rounded-2xl bg-primary text-white shadow-lg shadow-primary/20 flex items-center justify-center active:scale-90 transition-all disabled:grayscale disabled:opacity-30">
                            <Plus :size="20" stroke-width="4" />
                        </button>
                    </div>
                </div>

                <div class="mt-12 p-10 bg-card border-2 border-dashed border-border rounded-[3.5rem] text-center">
                    <div class="flex justify-center mb-4 text-muted-foreground/30">
                        <Info :size="32" />
                    </div>
                    <h4 class="text-sm font-black uppercase tracking-widest mb-2 italic">Flujo de Carrito Inteligente</h4>
                    <p class="text-xs font-medium text-muted-foreground leading-relaxed max-w-sm mx-auto">
                        Los productos se añaden de forma independiente. Puedes terminar tu compra desde el carrito global en cualquier momento.
                    </p>
                </div>
            </div>
        </div>
    </ShopLayout>
</template>

<style scoped>
.transition-all {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 300ms;
}

button {
    user-select: none;
    -webkit-tap-highlight-color: transparent;
}
</style>