<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { Plus, Minus, Zap, Heart, Loader2 } from 'lucide-vue-next';

const props = defineProps({
    sku: { type: Object, required: true },
    loading: { type: Boolean, default: false },
    isActive: { type: Boolean, default: false }
});
// Añadir esta utilidad en el script si los precios pueden venir como string desde la DB
const formatPrice = (value) => parseFloat(value || 0).toFixed(2);
const page = usePage();

// --- LÓGICA DE IDENTIDAD Y AUTH ---
const user = computed(() => page.props.auth?.customer);
const isAuth = computed(() => !!user.value);

// --- ESTADO DE FAVORITOS ---
const localFavorites = ref(JSON.parse(localStorage.getItem('guest_favorites') || '[]'));

const isFavorite = computed(() => {
    if (!props.sku) return false;
    
    const productId = props.sku.product_id; // <--- USAR ID DE PRODUCTO

    if (isAuth.value) {
        return user.value.favorites_ids?.includes(productId);
    }
    // Para Guest, comparamos contra el ID de producto guardado localmente
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
        console.log("DEBUG SKU:", props.sku)
        router.post(route('customer.favorites.toggle'), { sku_id: props.sku.id }, {
            preserveScroll: true, preserveState: true
            
        });
    } else {
        let favs = JSON.parse(localStorage.getItem('guest_favorites') || '[]');
        const index = favs.findIndex(f => f.id === props.sku.id);

        if (index > -1) {
            favs.splice(index, 1);
        } else {
            favs.push({
                id: props.sku.id,
                name: props.sku.name,
                image: props.sku.image,
                final_price: props.sku.final_price,
                list_price: props.sku.list_price,
                bg_color: props.sku.bg_color,
                brand_name: props.sku.brand_name
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

const isProcessing = ref(false); // Estado para Skeletons/Spinners locales
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
        // BLINDAJE: Si hay un error, también liberamos el estado de carga
        onError: () => {
            isProcessing.value = false;
        },
        onFinish: () => {
            isProcessing.value = false;
        }
    });
};

const updateQty = (delta) => {
    if (isProcessing.value) return;

    const newQty = quantity.value + delta;
    
    // REGLA: No permitir más que el stock disponible
    if (delta > 0 && newQty > props.sku.stock) {
        // Podrías disparar un Toast aquí: "Máximo stock alcanzado"
        return;
    }

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

// --- IDENTIDAD VISUAL ---
const dynamicStyle = computed(() => ({
    '--local-sku-color': props.sku?.bg_color || 'var(--primary)',
}));

const goToProduct = () => {
    if (props.loading || !props.sku) return;
    router.visit(route('customer.shop.product', { id: props.sku.id }));
};
</script>

<template>
    <div v-if="!loading && sku" 
        @click="goToProduct"
        :style="dynamicStyle"
        class="group relative flex flex-col bg-card border border-border/40 rounded-3xl overflow-hidden transition-all duration-500 hover:shadow-2xl hover:shadow-[var(--local-sku-color)]/20 active:scale-[0.98]"
        :class="{ 'ring-2 ring-[var(--local-sku-color)] border-transparent': quantity > 0 || isActive }">
        
        <button @click="toggleFavorite" 
                class="absolute top-3 right-3 z-30 p-2 rounded-full backdrop-blur-md bg-black/10 border border-white/10 transition-all hover:scale-110 active:scale-90 group/heart">
            <Heart :size="16" 
                :stroke-width="isFavorite ? 0 : 2.5"
                :class="isFavorite ? 'text-primary fill-primary' : 'text-white'" />
        </button>

        <div class="h-1 w-full relative z-20" :style="{ backgroundColor: 'var(--local-sku-color)' }"></div>

        <div class="p-3 flex flex-col h-full relative z-10">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[9px] font-black uppercase tracking-[0.2em] text-foreground/80 truncate">
                    {{ sku.brand_name || 'CyberMarket' }}
                </span>
                <div v-if="sku.stock <= 5 && sku.stock > 0" class="flex items-center gap-1">
                    <Zap :size="10" class="text-accent fill-accent" />
                    <span class="text-[8px] font-black text-accent uppercase italic">Low_Stock</span>
                </div>
            </div>

            <div class="aspect-square relative rounded-2xl overflow-hidden mb-3 shadow-inner border border-white/10 static-split-bg p-4">
                <img :src="sku.image" 
                     class="relative z-20 w-full h-full object-contain transition-transform duration-700 group-hover:scale-110 drop-shadow-2xl"
                     :alt="sku.name">
                
                <div v-if="hasDiscount" class="absolute top-2 left-2 z-30">
                    <span class="bg-primary text-white text-[8px] font-black px-1.5 py-0.5 rounded-md shadow-lg uppercase italic">
                        -{{ sku.discount_percentage }}%
                    </span>
                </div>

                <div class="absolute bottom-2 right-2 z-30">
                    <button v-if="quantity === 0" 
                            @click.stop="handleAdd"
                            :disabled="sku.stock <= 0 || isProcessing"
                            class="w-10 h-10 bg-white/20 backdrop-blur-xl rounded-full flex items-center justify-center border border-white/20 shadow-xl transition-all"
                            :class="sku.stock <= 0 ? 'opacity-50 grayscale cursor-not-allowed' : 'hover:bg-[var(--local-sku-color)] hover:text-white'">
                        <Loader2 v-if="isProcessing" :size="18" class="animate-spin" />
                        <Plus v-else :size="18" stroke-width="3" />
                    </button>

                    <div v-else @click.stop class="flex items-center bg-[var(--local-sku-color)] text-white rounded-full p-0.5 shadow-lg">
                        <button @click="updateQty(-1)" :disabled="isProcessing" class="w-7 h-7 flex items-center justify-center rounded-full hover:bg-white/20">
                            <Minus :size="14" stroke-width="3"/>
                        </button>
                        <span class="w-6 text-center font-mono font-black text-xs">
                            <Loader2 v-if="isProcessing" :size="10" class="animate-spin mx-auto" />
                            <template v-else>{{ quantity }}</template>
                        </span>
                        <button @click="updateQty(1)" 
                                :disabled="isProcessing || quantity >= sku.stock" 
                                class="w-7 h-7 flex items-center justify-center rounded-full hover:bg-white/20 disabled:opacity-30">
                            <Plus :size="14" stroke-width="3"/>
                        </button>
                    </div>
                </div>
                <div v-if="sku.stock <= 0" class="absolute inset-0 bg-background/60 backdrop-blur-[2px] z-40 flex items-center justify-center pointer-events-none">
                    <span class="bg-destructive text-white text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-widest">Agotado</span>
                </div>
            </div>

            <div class="flex-1 flex flex-col justify-between">
                <h3 class="text-[11px] font-black uppercase leading-[1.2] tracking-tight text-foreground line-clamp-2 mb-2 group-hover:text-[var(--local-sku-color)] transition-colors">
                    {{ sku.name }}
                </h3>
                
                <div v-if="sku.upsell?.next_price" class="mt-1 mb-2">
                    <div class="flex items-center gap-1 bg-[var(--local-sku-color)]/10 px-2 py-0.5 rounded-md border border-[var(--local-sku-color)]/20 w-fit">
                        <Zap :size="10" class="text-[var(--local-sku-color)]" />
                        <span class="text-[8px] font-black text-[var(--local-sku-color)] uppercase tracking-tighter">
                            Bs {{ sku.upsell.next_price.toFixed(2) }} desde {{ sku.upsell.next_qty }} unid
                        </span>
                    </div>
                </div>

                <div class="space-y-0.5">
                    <span v-if="hasDiscount" class="text-[9px] font-bold text-muted-foreground/50 line-through block leading-none font-mono">
                        {{ (sku.list_price || 0).toFixed(2) }}
                    </span>
                    <div class="flex items-baseline gap-1">
                        <span class="text-[9px] font-black text-[var(--local-sku-color)] uppercase">Bs</span>
                        <span class="text-xl font-black tracking-tighter text-foreground font-mono leading-none">
                            {{ (sku.final_price || 0).toFixed(2) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div v-else class="aspect-[3/4] bg-muted/20 border border-border/10 rounded-3xl animate-pulse"></div>
</template>

<style scoped>
.font-mono { font-family: 'JetBrains Mono', monospace; }
.static-split-bg {
    background-color: transparent;
    background-image: linear-gradient(135deg, var(--local-sku-color) 0%, hsl(var(--primary)) 100%);
    opacity: 1;
}
.dark .static-split-bg {
    background-image: linear-gradient(135deg, var(--local-sku-color), hsl(var(--primary)), #000000);
}
.shadow-f1-glow { box-shadow: 0 0 15px -3px var(--local-sku-color); }
</style>