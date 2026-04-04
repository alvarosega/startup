<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { Plus, Minus, Zap, Heart, Loader2, Trash2, LayoutGrid } from 'lucide-vue-next';

const props = defineProps({
    sku: { type: Object, required: true },
    loading: { type: Boolean, default: false },
    isActive: { type: Boolean, default: false }
});

const formatPrice = (value) => parseFloat(value || 0).toFixed(2);
const page = usePage();

// --- LÓGICA DE IDENTIDAD Y AUTH ---
const user = computed(() => page.props.auth?.customer);
const isAuth = computed(() => !!user.value);

// --- ESTADO DE FAVORITOS ---
const localFavorites = ref(JSON.parse(localStorage.getItem('guest_favorites') || '[]'));

const isFavorite = computed(() => {
    if (!props.sku) return false;
    const productId = props.sku.product_id;
    if (isAuth.value) return user.value.favorites_ids?.includes(productId);
    return localFavorites.value.some(fav => fav.product_id === productId); 
});

const updateLocalFavs = () => {
    localFavorites.value = JSON.parse(localStorage.getItem('guest_favorites') || '[]');
};

onMounted(() => window.addEventListener('local-favorites-updated', updateLocalFavs));
onUnmounted(() => window.removeEventListener('local-favorites-updated', updateLocalFavs));

const toggleFavorite = (e) => {
    e.stopPropagation();
    if (props.loading || !props.sku) return;

    if (isAuth.value) {
        router.post(route('customer.favorites.toggle'), { 
            product_id: props.sku.product_id 
        }, { preserveScroll: true, preserveState: true });
    } else {
        let favs = JSON.parse(localStorage.getItem('guest_favorites') || '[]');
        const productId = props.sku.product_id;
        const index = favs.findIndex(f => f.product_id === productId);

        if (index > -1) {
            favs.splice(index, 1);
        } else {
            favs.push({ 
                id: props.sku.id,
                product_id: productId,
                name: props.sku.name,
                image: props.sku.image,
                brand_name: props.sku.brand_name,
                bg_color: props.sku.bg_color
            });
        }
        localStorage.setItem('guest_favorites', JSON.stringify(favs));
        window.dispatchEvent(new Event('local-favorites-updated'));
    }
};

// --- LÓGICA DE CARRITO ---
const cartItem = computed(() => {
    if (!props.sku) return null;
    const items = page.props.cart?.items || [];
    return items.find(item => item.sku_id === props.sku.id);
});

const quantity = computed(() => cartItem.value ? cartItem.value.quantity : 0);
const hasDiscount = computed(() => props.sku?.list_price > props.sku?.final_price);

const isProcessing = ref(false); 
const handleAdd = () => {
    if (props.loading || isProcessing.value) return;
    
    isProcessing.value = true;
    router.post(route('customer.cart.upsert'), {
        target_id: props.sku.id,
        target_type: 'sku',
        quantity: 1
    }, { 
        preserveScroll: true, 
        preserveState: true,
        onError: () => { isProcessing.value = false; },
        onFinish: () => { isProcessing.value = false; }
    });
};

const updateQty = (delta) => {
    if (isProcessing.value) return;

    const newQty = quantity.value + delta;
    if (delta > 0 && newQty > props.sku.stock) return;

    isProcessing.value = true;
    if (newQty < 1) {
        router.delete(route('customer.cart.remove', cartItem.value.id), { 
            preserveScroll: true,
            onFinish: () => isProcessing.value = false 
        });
    } else {
        router.patch(route('customer.cart.update', cartItem.value.id), { 
            quantity: newQty 
        }, { 
            preserveScroll: true,
            onFinish: () => isProcessing.value = false 
        });
    }
};

const dynamicStyle = computed(() => ({
    '--local-sku-color': props.sku?.bg_color || 'var(--primary)',
}));

const goToProduct = () => {
    if (props.loading || !props.sku) return;
    router.visit(route('customer.product', { id: props.sku.id }));
};
</script>

<template>
    <div v-if="!loading && sku" 
        @click="goToProduct"
        :style="dynamicStyle"
        class="glass-chassis group relative flex flex-col justify-between overflow-hidden cursor-pointer transition-transform duration-300 active:scale-[0.97] outline-none"
        :class="{ 'ring-2 ring-primary ring-offset-2 ring-offset-background': quantity > 0 || isActive }">
        
        <button @click.stop="toggleFavorite"
                class="absolute top-4 right-4 z-30 p-2.5 rounded-full bg-background/50 backdrop-blur-md border border-border/50 transition-all duration-300 hover:scale-110 hover:bg-background/80 active:scale-90 shadow-sm">
            <Heart :size="16" 
                :stroke-width="isFavorite ? 0 : 2.5"
                :class="isFavorite ? 'text-primary fill-primary drop-shadow-md' : 'text-foreground/70'" />
        </button>

        <div class="p-4 pb-0 flex flex-col relative z-10">
            <div class="flex items-start justify-between mb-4">
                <span class="text-[9px] font-black uppercase tracking-[0.2em] text-foreground/60 line-clamp-1 pr-8">
                    {{ sku.brand_name || 'DIGITAL UNIT' }}
                </span>
                
                <div v-if="sku.stock <= 5 && sku.stock > 0" 
                     class="flex items-center gap-1 bg-accent/20 px-1.5 py-0.5 rounded border border-accent/30 shadow-sm">
                    <Zap :size="10" class="text-accent-foreground fill-accent-foreground animate-pulse" />
                    <span class="text-[8px] font-black text-accent-foreground uppercase tracking-widest">Low Stock</span>
                </div>
                <div v-else-if="sku.stock <= 0" 
                     class="bg-destructive/10 px-2 py-1 rounded border border-destructive/20 shadow-sm">
                    <span class="text-[8px] font-black text-destructive uppercase tracking-widest">Agotado</span>
                </div>
            </div>

            <div class="aspect-square relative rounded-[1.5rem] overflow-hidden mb-4 sku-stage-bg p-5 flex items-center justify-center transition-colors duration-500">
                
                <img :src="sku.image" 
                    class="relative z-20 w-full h-full object-contain transition-transform duration-700 ease-out group-hover:scale-110 drop-shadow-[0_20px_25px_rgba(0,0,0,0.6)]"
                    :alt="sku.name">
                
                <div v-if="hasDiscount" class="absolute top-3 left-3 z-30">
                    <span class="bg-primary text-primary-foreground text-[10px] font-black px-2.5 py-1 rounded-lg shadow-xl uppercase italic tracking-tighter">
                        -{{ sku.discount_percentage }}%
                    </span>
                </div>

                <div v-if="sku.stock <= 0" class="absolute inset-0 bg-black/60 backdrop-blur-sm z-40 flex items-center justify-center pointer-events-none"></div>
            </div>

            <div class="flex-1 flex flex-col">
                <div v-if="sku.upsell?.next_price" class="mb-3">
                    <div class="inline-flex items-center gap-1.5 bg-accent text-accent-foreground px-2.5 py-1 rounded-md shadow-sm border border-accent/20">
                        <Zap :size="10" class="fill-current" />
                        <span class="text-[8.5px] font-black uppercase tracking-widest">
                            Bs {{ formatPrice(sku.upsell.next_price) }} DESDE {{ sku.upsell.next_qty }} UNID
                        </span>
                    </div>
                </div>

                <h3 class="text-xs font-black uppercase leading-snug tracking-tight text-foreground line-clamp-2 mb-4">
                    {{ sku.name }}
                </h3>
            </div>
        </div>

        <div class="p-4 pt-0 mt-auto relative z-10 space-y-4">
            
            <div class="flex flex-col">
                <span v-if="hasDiscount" class="text-[10px] font-bold text-muted-foreground line-through leading-none font-mono mb-0.5">
                    {{ formatPrice(sku.list_price) }}
                </span>
                <div class="flex items-baseline gap-1">
                    <span class="text-[10px] font-black text-foreground/70 uppercase">Bs</span>
                    <span class="text-[32px] font-black tracking-tighter text-foreground font-mono leading-none">
                        {{ formatPrice(sku.final_price) }}
                    </span>
                </div>
            </div>

            <div class="relative h-12 w-full">
                <button v-if="quantity === 0" 
                        @click.stop="handleAdd"
                        :disabled="sku.stock <= 0 || isProcessing"
                        class="w-full h-full bg-foreground text-background rounded-[1rem] flex items-center justify-center gap-2 transition-all duration-300 active:scale-95 disabled:opacity-40 shadow-lg hover:opacity-90">
                    <Loader2 v-if="isProcessing" :size="16" class="animate-spin" />
                    <Plus v-else :size="16" stroke-width="4" />
                    <span class="text-[10px] font-black uppercase tracking-[0.15em] pt-0.5">Añadir al carrito</span>
                </button>

                <div v-else @click.stop 
                     class="w-full h-full bg-foreground/5 backdrop-blur-md rounded-[1rem] border border-border/40 flex items-center justify-between px-1.5 shadow-inner">
                    
                    <button @click="updateQty(-1)" 
                            :disabled="isProcessing" 
                            class="w-10 h-9 flex items-center justify-center rounded-lg hover:bg-foreground/10 transition-colors group/btn">
                        <Trash2 v-if="quantity === 1" :size="16" stroke-width="2.5" class="text-foreground group-hover/btn:text-destructive transition-colors" />
                        <Minus v-else :size="16" stroke-width="4" class="text-foreground" />
                    </button>

                    <div class="flex flex-col items-center justify-center">
                        <Loader2 v-if="isProcessing" :size="14" class="animate-spin text-foreground/50" />
                        <span v-else class="font-mono font-black text-foreground text-base leading-none">{{ quantity }}</span>
                    </div>

                    <button @click="updateQty(1)" 
                            :disabled="isProcessing || quantity >= sku.stock" 
                            class="w-10 h-9 flex items-center justify-center rounded-lg hover:bg-foreground/10 transition-colors disabled:opacity-20">
                        <Plus :size="16" stroke-width="4" class="text-foreground" />
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div v-else class="aspect-[3/5] bg-secondary border border-border/50 rounded-[1.5rem] animate-pulse relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-foreground/5 to-transparent -translate-x-full animate-[shimmer_2s_infinite]"></div>
    </div>
</template>

<style scoped>
.font-mono { font-family: 'JetBrains Mono', monospace; }

/* 1. CHASIS DE CRISTAL DIFUMINADO (Difumina la malla, deja pasar color) */
.glass-chassis {
    width: 100%;
    height: 100%;
    /* 40% de opacidad para que el Blur haga su trabajo destructivo sobre la malla */
    background-color: hsl(var(--background) / 0.4);
    backdrop-filter: blur(40px) saturate(200%);
    -webkit-backdrop-filter: blur(40px) saturate(200%);
    border: 1px solid hsl(var(--border) / 0.4);
    border-radius: 1.5rem;
    box-shadow: 0 10px 30px -10px rgba(0,0,0,0.1);
}

.dark .glass-chassis {
    background-color: hsl(var(--background) / 0.6);
    border-color: hsl(var(--border) / 0.6);
    box-shadow: 0 10px 30px -10px rgba(0,0,0,0.4);
}

/* 2. PUNTO FOCAL INDEPENDIENTE: Escenario Uiverse Flotante */
.sku-stage-bg {
    background-color: var(--local-sku-color);
    background-image: linear-gradient(
        43deg, 
        var(--local-sku-color) 0%, 
        hsl(var(--primary)) 46%, 
        hsl(var(--accent)) 100%
    );
    
    /* Sombra interna (Uiverse) + Sombra externa masiva (FLOTACIÓN SOBRE EL CRISTAL) */
    box-shadow: 
        rgba(0, 0, 0, 0.17) 0px -23px 25px 0px inset, 
        rgba(0, 0, 0, 0.15) 0px -36px 30px 0px inset, 
        rgba(0, 0, 0, 0.1) 0px -79px 40px 0px inset,
        inset 0 4px 10px rgba(255, 255, 255, 0.3), /* Highlights de Hardware */
        0 25px 35px -15px rgba(0, 0, 0, 0.4); /* Flota sobre el cristal esmerilado */
        
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.dark .sku-stage-bg {
    /* Conserva la explosión de color pero apaga el fondo para contraste oscuro */
    background-image: linear-gradient(
        43deg, 
        var(--local-sku-color) 0%, 
        hsl(var(--primary)) 46%, 
        #111111 100%
    );
    box-shadow: 
        rgba(0, 0, 0, 0.4) 0px -23px 25px 0px inset, 
        rgba(0, 0, 0, 0.4) 0px -36px 30px 0px inset, 
        rgba(0, 0, 0, 0.3) 0px -79px 40px 0px inset,
        inset 0 4px 10px rgba(255, 255, 255, 0.1),
        0 25px 35px -15px rgba(0, 0, 0, 0.7);
    border-color: rgba(255, 255, 255, 0.05);
}
</style>