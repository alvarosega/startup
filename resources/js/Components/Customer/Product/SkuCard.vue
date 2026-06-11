<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { Plus, Minus, Zap, Heart, Loader2, Trash2, CheckCircle2 } from 'lucide-vue-next';

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
    if (!props.sku || !page.props.cart) return null;
    const source = page.props.cart.items;
    const items = Array.isArray(source) ? source : (source?.data || Object.values(source || {}));
    return items.find(item => String(item.sku_id) === String(props.sku.id));
});

const quantity = computed(() => cartItem.value ? cartItem.value.quantity : 0);

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
    if (props.loading || isProcessing.value || props.sku.stock <= 0) return;
    
    isProcessing.value = true;
    router.post(route('customer.cart.upsert'), {
        target_id: props.sku.id,
        target_type: 'sku',
        quantity: 1
    }, { 
        preserveScroll: true, 
        preserveState: true,
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

const isSuggestionMet = computed(() => {
    return props.suggestedQuantity > 0 && quantity.value >= props.suggestedQuantity;
});

const goToProduct = () => {
    if (props.loading || !props.sku) return;
    // CORRECCIÓN: Cambiar props.sku.id por props.sku.product_id
    router.visit(route('customer.product.show', { id: props.sku.product_id }));
};

// --- ALGORITMO DE DEGRADADO LINEAL AL 15% ---
const getSkuStyle = computed(() => {
    if (!props.sku) return {};
    
    let cleanHex = props.sku.bg_color ? props.sku.bg_color.replace('#', '') : '32323b';
    
    if (cleanHex.length === 3) {
        cleanHex = cleanHex.split('').map(c => c + c).join('');
    }

    const r = parseInt(cleanHex.substring(0, 2), 16) || 0;
    const g = parseInt(cleanHex.substring(2, 4), 16) || 0;
    const b = parseInt(cleanHex.substring(4, 6), 16) || 0;

    const dr = Math.floor(r * 0.15);
    const dg = Math.floor(g * 0.15);
    const db = Math.floor(b * 0.15);

    const darkHex = [dr, dg, db].map(x => x.toString(16).padStart(2, '0')).join('');

    return {
        background: `linear-gradient(to bottom, #${cleanHex} 0%, #${darkHex} 100%)`
    };
});
</script>

<template>
    <div v-if="!loading && sku"
        @click="goToProduct"
        :style="getSkuStyle"
        class="group relative flex flex-col justify-between min-h-[400px] border border-[#32323b] rounded-xl overflow-hidden cursor-pointer outline-none transition-transform duration-150 ease-f1 active:scale-95"
        :class="{ 'ring-2 ring-primary ring-offset-2 ring-offset-[#15151f]': quantity > 0 || isActive }">
        
        <button @click.stop="toggleFavorite"
                class="absolute top-3 right-3 z-30 p-2 bg-[#15151f]/80 backdrop-blur-md border border-[#32323b] rounded-md transition-all duration-150 hover:bg-[#15151f] active:scale-95 shadow-sm outline-none">
            <Heart :size="16" 
                :stroke-width="isFavorite ? 0 : 1.5"
                :class="isFavorite ? 'text-primary fill-primary' : 'text-white'" />
        </button>

        <div class="p-4 pb-0 flex flex-col relative z-10 flex-1">
            <div class="flex items-start justify-between mb-2">
                <span class="text-[9px] font-black uppercase tracking-[0.2em] text-white/60 line-clamp-1 pr-10">
                    {{ sku.brand_name || 'DIGITAL UNIT' }}
                </span>
                
                <div v-if="sku.stock <= 5 && sku.stock > 0" class="flex items-center gap-1 bg-[#15151f] px-1.5 py-0.5 rounded border border-yellow-500/50">
                    <Zap :size="10" class="text-yellow-500 fill-yellow-500" />
                    <span class="text-[8px] font-black text-yellow-500 uppercase tracking-widest">Low Stock</span>
                </div>
                <div v-else-if="sku.stock <= 0" class="bg-red-500/20 px-1.5 py-0.5 rounded border border-red-500/50">
                    <span class="text-[8px] font-black text-white uppercase tracking-widest">Agotado</span>
                </div>
            </div>

            <div class="relative w-full h-[180px] mb-4 flex items-center justify-center p-2 rounded-lg">
                <img :src="sku.image" 
                    class="absolute inset-0 w-full h-full object-contain p-4 transition-transform duration-500 ease-f1 group-hover:scale-110 drop-shadow-hardware z-20"
                    :alt="sku.name">
                
                <div v-if="hasDiscount" class="absolute top-2 left-2 z-30">
                    <span class="bg-primary text-white text-[10px] font-black px-2 py-1 rounded shadow-sm uppercase tracking-tight border border-[#15151f]/20">
                        -{{ sku.discount_percentage || ((currentListPrice - currentPrice) / currentListPrice * 100).toFixed(0) }}%
                    </span>
                </div>

                <div v-if="sku.stock <= 0" class="absolute inset-0 bg-black/60 z-40 flex items-center justify-center pointer-events-none rounded-lg"></div>

                <div v-if="suggestedQuantity > 0" class="absolute -top-1 -left-1 z-30 flex flex-col items-start gap-1">
                    <span class="bg-[#15151f] text-white text-[8px] font-black px-2 py-1 rounded-br-lg shadow-lg uppercase tracking-tighter border-b border-r border-[#32323b]">
                        Sugerido: {{ suggestedQuantity }}u
                    </span>
                    <div v-if="isSuggestionMet" class="ml-1 bg-emerald-500 text-white p-0.5 rounded-full shadow-lg border border-[#15151f]">
                        <CheckCircle2 :size="10" stroke-width="3" />
                    </div>
                </div>
            </div>

            <div class="flex-1 flex flex-col justify-end">
                <div v-if="currentUpsell && (currentUpsell.next_price || currentUpsell.potential_price)" class="mb-3">
                    <div class="inline-flex items-center gap-1 bg-[#15151f] text-white px-2 py-1 rounded shadow-sm border border-[#32323b]">
                        <Zap :size="10" class="fill-current text-primary" />
                        <span class="text-[8px] font-black uppercase tracking-widest">
                            Bs {{ formatPrice(currentUpsell.potential_price || currentUpsell.next_price) }} DESDE {{ currentUpsell.needed_quantity || currentUpsell.next_qty }} UNID
                        </span>
                    </div>
                </div>

                <h3 class="text-[11px] font-black uppercase leading-tight tracking-tight text-white line-clamp-2 mb-3">
                    {{ sku.name }}
                </h3>
            </div>
        </div>

        <div class="px-4 pb-3 relative z-10">
            <div class="flex flex-col">
                <span v-if="hasDiscount" class="text-[10px] font-bold text-red-500 line-through leading-none font-mono mb-0.5">
                    {{ formatPrice(currentListPrice) }}
                </span>
                <div class="flex items-baseline gap-1">
                    <span class="text-[10px] font-black text-white/70 uppercase">Bs</span>
                    <span class="text-2xl font-black tracking-tighter text-white font-mono leading-none">
                        {{ formatPrice(currentPrice) }}
                    </span>
                </div>
            </div>
        </div>

        <div class="w-full h-12 mt-auto border-t border-[#32323b] bg-[#15151f] relative z-20">
            
            <button v-if="quantity === 0" 
                    @click.stop="handleAdd"
                    :disabled="sku.stock <= 0 || isProcessing"
                    class="w-full h-full flex items-center justify-center gap-2 bg-transparent text-white hover:bg-primary transition-colors disabled:opacity-50 disabled:hover:bg-transparent outline-none focus:outline-none group">
                <Loader2 v-if="isProcessing" :size="16" class="animate-spin" />
                <Plus v-else :size="18" :stroke-width="2" class="group-hover:scale-110 transition-transform" />
                <span class="text-[11px] font-black uppercase tracking-[0.2em] pt-0.5">Añadir</span>
            </button>

            <div v-else @click.stop class="w-full h-full flex items-center justify-between px-2 bg-primary">
                
                <button @click.stop="updateQty(-1)" 
                        :disabled="isProcessing" 
                        class="w-10 h-8 flex items-center justify-center bg-black/20 hover:bg-black/40 rounded transition-colors active:scale-95 outline-none focus:outline-none">
                    <Trash2 v-if="quantity === 1" :size="14" :stroke-width="2" class="text-white" />
                    <Minus v-else :size="16" :stroke-width="2" class="text-white" />
                </button>

                <div class="flex flex-col items-center justify-center">
                    <Loader2 v-if="isProcessing" :size="14" class="animate-spin text-white" />
                    <span v-else class="font-mono font-black text-white text-base leading-none">{{ quantity }}</span>
                </div>

                <button @click.stop="updateQty(1)" 
                        :disabled="isProcessing || quantity >= sku.stock" 
                        class="w-10 h-8 flex items-center justify-center bg-black/20 hover:bg-black/40 rounded transition-colors active:scale-95 disabled:opacity-40 outline-none focus:outline-none">
                    <Plus :size="16" :stroke-width="2" class="text-white" />
                </button>
            </div>
        </div>
    </div>

    <div v-else class="min-h-[400px] bg-[#15151f] border border-[#32323b] rounded-xl animate-pulse relative overflow-hidden"></div>
</template>

<style scoped>
.font-mono { font-family: 'JetBrains Mono', monospace; }
.ease-f1 { transition-timing-function: cubic-bezier(0.16, 1, 0.3, 1); }
.drop-shadow-hardware { filter: drop-shadow(0 15px 15px rgba(0,0,0,0.5)); }
</style>