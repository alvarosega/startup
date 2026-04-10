<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { Plus, Minus, Zap, Heart, Loader2, Trash2, LayoutGrid, CheckCircle2 } from 'lucide-vue-next';

const props = defineProps({
    sku: { type: Object, required: true },
    loading: { type: Boolean, default: false },
    isActive: { type: Boolean, default: false },
    suggestedQuantity: { type: Number, default: 0 }
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

const cartItem = computed(() => {
    // 1. Si no hay SKU o el carrito aún no carga (defer), abortar.
    if (!props.sku || !page.props.cart) return null;
    
    // 2. Extraer los items con fallback por si llegan envueltos o como objeto
    const source = page.props.cart.items;
    const items = Array.isArray(source) ? source : (source?.data || Object.values(source || {}));
    
    // 3. Búsqueda estricta
    return items.find(item => String(item.sku_id) === String(props.sku.id));
});
const quantity = computed(() => cartItem.value ? cartItem.value.quantity : 0);

// RECTIFICACIÓN: Lectura correcta de props.sku.final_price
const currentPrice = computed(() => {
    return cartItem.value ? Number(cartItem.value.unit_price) : Number(props.sku.final_price);
});

const currentListPrice = computed(() => {
    return cartItem.value ? Number(cartItem.value.list_price) : Number(props.sku.list_price);
});

const currentUpsell = computed(() => {
    return cartItem.value ? cartItem.value.upsell : props.sku.upsell;
});

const hasDiscount = computed(() => currentListPrice.value > currentPrice.value);
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
        // CIRUGÍA: Solo pedimos que el servidor nos devuelva el carrito y los mensajes flash
        only: ['cart', 'flash'], 
        onError: () => { isProcessing.value = false; },
        onFinish: () => { isProcessing.value = false; }
    });
};

const updateQty = (delta) => {
    if (isProcessing.value) return;

    const newQty = quantity.value + delta;
    if (delta > 0 && newQty > props.sku.stock) return;

    isProcessing.value = true;
    
    const options = {
        preserveScroll: true,
        preserveState: true,
        // CIRUGÍA: Evitamos que se recarguen los productos y skeletons
        only: ['cart', 'flash'],
        onFinish: () => isProcessing.value = false 
    };

    if (newQty < 1) {
        router.delete(route('customer.cart.remove', cartItem.value.id), options);
    } else {
        router.patch(route('customer.cart.update', cartItem.value.id), { 
            quantity: newQty 
        }, options);
    }
};
const dynamicStyle = computed(() => ({
    '--local-sku-color': props.sku?.bg_color || 'var(--primary)',
}));

const isSuggestionMet = computed(() => {
    return props.suggestedQuantity > 0 && quantity.value >= props.suggestedQuantity;
});

const goToProduct = () => {
    if (props.loading || !props.sku) return;
    router.visit(route('customer.product.show', { id: props.sku.id }));
};
</script>

<template>
    <div v-if="!loading && sku"
        @click="goToProduct"
        :style="dynamicStyle"
       class="glass-chassis group relative flex flex-col justify-between min-h-[400px] overflow-hidden cursor-pointer transition-transform duration-300 active:scale-[0.97] outline-none"
        :class="{ 'ring-2 ring-primary ring-offset-2 ring-offset-background': quantity > 0 || isActive }">
        
        <button @click.stop="toggleFavorite"
                class="absolute top-4 right-4 z-30 p-2.5 rounded-full bg-background/50 backdrop-blur-md border border-border/50 transition-all duration-300 hover:scale-110 hover:bg-background/80 active:scale-90 shadow-sm">
            <Heart :size="16" 
                :stroke-width="isFavorite ? 0 : 2.5"
                :class="isFavorite ? 'text-primary fill-primary drop-shadow-md' : 'text-foreground/70'" />
        </button>

        <div class="p-4 pb-0 flex flex-col relative z-10">
            <div class="flex items-start justify-between mb-4">
                <span class="text-xs font-black uppercase tracking-[0.2em] text-foreground/50 line-clamp-1 pr-8">
                    {{ sku.brand_name || 'DIGITAL UNIT' }}
                </span>
                
                <div v-if="sku.stock <= 5 && sku.stock > 0" 
                    class="flex items-center gap-1 bg-accent/20 px-2 py-0.5 rounded-lg border border-accent/30">
                    <Zap :size="10" class="text-accent-foreground fill-accent-foreground animate-pulse" />
                    <span class="text-[10px] font-black text-accent-foreground uppercase tracking-widest">Low Stock</span>
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
                        -{{ sku.discount_percentage || ((currentListPrice - currentPrice) / currentListPrice * 100).toFixed(0) }}%
                    </span>
                </div>

                <div v-if="sku.stock <= 0" class="absolute inset-0 bg-black/60 backdrop-blur-sm z-40 flex items-center justify-center pointer-events-none"></div>

                <div v-if="suggestedQuantity > 0" 
                    class="absolute -top-1 -left-1 z-30 flex flex-col items-start gap-1">
                    <span class="bg-accent text-accent-foreground text-[8px] font-black px-2 py-1 rounded-br-xl shadow-lg uppercase tracking-tighter border-b border-r border-white/20">
                        Sugerido: {{ suggestedQuantity }}u
                    </span>
                    <div v-if="isSuggestionMet" class="ml-1 bg-emerald-500 text-white p-0.5 rounded-full shadow-lg border-2 border-white dark:border-neutral-900 animate-in zoom-in">
                        <CheckCircle2 :size="10" stroke-width="4" />
                    </div>
                </div>
            </div>

            <div class="flex-1 flex flex-col">
                <div v-if="currentUpsell && (currentUpsell.next_price || currentUpsell.potential_price)" class="mb-3">
                    <div class="inline-flex items-center gap-1.5 bg-accent text-accent-foreground px-2.5 py-1 rounded-md shadow-sm border border-accent/20">
                        <Zap :size="10" class="fill-current" />
                        <span class="text-[8.5px] font-black uppercase tracking-widest">
                            Bs {{ formatPrice(currentUpsell.potential_price || currentUpsell.next_price) }} DESDE {{ currentUpsell.needed_quantity || currentUpsell.next_qty }} UNID
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
                <span v-if="hasDiscount" class="text-xs font-bold text-muted-foreground line-through leading-none font-mono mb-1">
                    {{ formatPrice(currentListPrice) }}
                </span>
                <div class="flex items-baseline gap-1">
                    <span class="text-xs font-black text-foreground/50 uppercase">Bs</span>
                    <span class="text-3xl font-black tracking-tighter text-foreground font-mono leading-none">
                        {{ formatPrice(currentPrice) }}
                    </span>
                </div>
            </div>

            <div class="relative h-12 w-full">
                <button v-if="quantity === 0" 
                        @click.stop="handleAdd"
                        :disabled="sku.stock <= 0 || isProcessing"
                        class="btn-primary w-full h-12 flex items-center justify-center gap-2 !rounded-2xl">
                    <Loader2 v-if="isProcessing" :size="16" class="animate-spin" />
                    <Plus v-else :size="18" stroke-width="4" />
                    <span class="text-xs font-black uppercase tracking-widest pt-0.5">Añadir</span>
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


.dark .glass-chassis {
    background-color: hsl(var(--background) / 0.6);
    border-color: hsl(var(--border) / 0.6);
    box-shadow: 0 10px 30px -10px rgba(0,0,0,0.4);
}

/* 2. PUNTO FOCAL INDEPENDIENTE */
/* REEMPLAZAR EN <style scoped> */
.sku-stage-bg {
    background-color: var(--local-sku-color);
    background-image: radial-gradient(
        circle at 50% 50%, 
        rgba(255, 255, 255, 0.15) 0%, 
        transparent 100%
    );
    border: 1px solid rgba(255, 255, 255, 0.1);
    /* Sombra suave Apple */
    box-shadow: var(--shadow-apple-soft);
}

.dark .sku-stage-bg {
    background-image: radial-gradient(
        circle at 50% 50%, 
        var(--local-sku-color) 0%, 
        rgba(0, 0, 0, 0.4) 100%
    );
    border: 1px solid rgba(255, 255, 255, 0.05);
}

/* REEMPLAZAR: Chassis de Cristal */
.glass-chassis {
    @apply product-card flex flex-col justify-between h-full w-full;
    background-color: hsl(var(--card) / 0.8);
    backdrop-filter: blur(20px);
}
</style>