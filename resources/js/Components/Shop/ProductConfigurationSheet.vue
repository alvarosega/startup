<script setup>
import { ref, computed, watch, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import { X, ShoppingCart, Plus, Minus, PackageX, Loader2, Zap } from 'lucide-vue-next';

const props = defineProps({
    product: { type: Object, default: null }
});

const emit = defineEmits(['close']);

const carouselRef = ref(null);
const currentSkuIndex = ref(0);
const quantity = ref(1);
const isSubmitting = ref(false);

const activeSku = computed(() => {
    if (!props.product || !props.product.skus) return null;
    return props.product.skus[currentSkuIndex.value];
});

watch(() => props.product, (newProduct) => {
    if (newProduct && newProduct.skus) {
        const sorted = [...newProduct.skus].sort((a, b) => a.price - b.price);
        const startIndex = newProduct.skus.findIndex(s => s.id === sorted[0].id);
        
        currentSkuIndex.value = startIndex !== -1 ? startIndex : 0;
        quantity.value = 1;
        
        nextTick(() => scrollToIndex(currentSkuIndex.value, 'instant'));
    }
});

const getImageUrl = (path) => {
    if (!path) return '/assets/img/placeholder.png';
    if (path.startsWith('http')) return path;
    return `/storage/${path.replace(/^\/+/, '')}`;
};

const getSkuPriceData = (sku, qty) => {
    if (!sku) return { price: 0, type: 'regular', next_tier: null };
    if (!sku.all_prices || sku.all_prices.length === 0) {
        return { price: sku.price || 0, type: 'regular', next_tier: null };
    }

    const winning = [...sku.all_prices]
        .filter(p => qty >= p.min_quantity)
        .sort((a, b) => b.priority - a.priority || b.min_quantity - a.min_quantity)[0];

    const next = [...sku.all_prices]
        .filter(p => p.min_quantity > qty && p.final_price < (winning?.final_price || sku.price))
        .sort((a, b) => a.min_quantity - b.min_quantity)[0];

    return {
        price: winning ? winning.final_price : sku.price,
        type: winning ? winning.type : 'regular',
        next_tier: next ? { min_qty: next.min_quantity, price: next.final_price } : null
    };
};

const currentPriceData = computed(() => getSkuPriceData(activeSku.value, quantity.value));

const scrollToIndex = (index, behavior = 'smooth') => {
    if (!carouselRef.value) return;
    const card = carouselRef.value.children[index];
    if (card) {
        card.scrollIntoView({ behavior, block: 'nearest', inline: 'center' });
        currentSkuIndex.value = index;
    }
};

const onCarouselScroll = () => {
    if (!carouselRef.value || !props.product) return;
    const scrollLeft = carouselRef.value.scrollLeft;
    const itemWidth = carouselRef.value.children[0].offsetWidth + 24; 
    const newIndex = Math.round(scrollLeft / itemWidth);
    
    if (newIndex !== currentSkuIndex.value && newIndex >= 0 && newIndex < props.product.skus.length) {
        currentSkuIndex.value = newIndex;
        quantity.value = 1; 
    }
};

const updateQty = (delta) => {
    if (!activeSku.value) return;
    const next = quantity.value + delta;
    if (next >= 1 && next <= activeSku.value.available_stock) {
        quantity.value = next;
    }
};

const addToCart = () => {
    if (!activeSku.value || activeSku.value.available_stock <= 0 || isSubmitting.value) return;

    isSubmitting.value = true;
    router.post(route('customer.cart.add'), { 
        sku_id: activeSku.value.id,
        quantity: quantity.value,
        guest_client_uuid: localStorage.getItem('guest_client_uuid')
    }, {
        preserveScroll: true,
        onSuccess: () => {
            isSubmitting.value = false;
            emit('close');
        },
        onError: () => isSubmitting.value = false
    });
};
</script>

<template>
    <div class="relative">
        <Transition name="fade">
            <div v-if="product" @click="emit('close')" class="fixed inset-0 z-[100] bg-black/40 dark:bg-black/90 backdrop-blur-md"></div>
        </Transition>

        <Transition name="slide-up">
            <div v-if="product" class="fixed inset-x-0 bottom-0 z-[110] bg-[#FFFFFF] dark:bg-[#050505] rounded-t-xl border-t border-gray-100 dark:border-[#262626] shadow-[0_-20px_60px_rgba(0,0,0,0.2)] flex flex-col max-h-[90vh] md:inset-0 md:m-auto md:w-full md:max-w-[440px] md:h-fit md:rounded-xl md:border">
                
                <div class="w-12 h-1.5 bg-gray-200 dark:bg-[#262626] rounded-full mx-auto mt-3 mb-1 md:hidden"></div>

                <div class="px-6 pt-4 pb-2 flex items-start justify-between">
                    <div class="flex-1">
                        <h3 class="text-xl font-bold tracking-[-0.02em] text-gray-900 dark:text-white leading-tight pr-8 capitalize">
                            {{ product.name }}
                        </h3>
                    </div>
                    <button @click="emit('close')" class="w-8 h-8 flex items-center justify-center rounded-lg bg-gray-50 dark:bg-[#121217] border border-transparent dark:border-[#262626] active:scale-90 transition-transform shrink-0">
                        <X class="w-4 text-gray-900 dark:text-white" stroke-width="2.5" />
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto no-scrollbar py-8">
                    <div ref="carouselRef" @scroll="onCarouselScroll" class="flex overflow-x-auto snap-x snap-mandatory no-scrollbar gap-6 px-[15%]">
                        <div v-for="(sku, index) in product.skus" :key="sku.id"
                             @click="scrollToIndex(index)"
                             class="w-[70vw] md:w-[260px] shrink-0 snap-center flex flex-col items-center transition-all duration-500 ease-out"
                             :class="currentSkuIndex === index ? 'opacity-100 scale-100' : 'opacity-20 scale-90 blur-[2px]'">
                            
                            <div class="relative w-full aspect-square flex items-center justify-center mb-8 overflow-visible">
                                
                                <div class="absolute inset-4 rounded-full transition-all duration-700 blur-[35px] opacity-40 dark:opacity-60"
                                     :class="currentSkuIndex === index ? 'scale-125' : 'scale-75 opacity-0'"
                                     :style="{ 
                                         background: 'radial-gradient(circle at center, var(--primary) 0%, transparent 70%)' 
                                     }"></div>
                                
                                <img :src="getImageUrl(sku.image_url)" 
                                     class="relative z-10 w-full h-full object-contain transition-all duration-700"
                                     :class="[
                                         currentSkuIndex === index ? 'scale-110 drop-shadow-[0_20px_30px_rgba(0,0,0,0.15)]' : 'scale-90',
                                         'dark:drop-shadow-none'
                                     ]">
                            </div>

                            <span class="text-[10px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-600 mb-1">Variante Seleccionada</span>
                            <span class="text-sm font-semibold tracking-[-0.01em] text-gray-900 dark:text-white text-center">{{ sku.name }}</span>
                        </div>
                    </div>

                    <div class="flex justify-center gap-1.5 mt-8">
                        <div v-for="(_, i) in product.skus" :key="i" 
                             class="h-1 rounded-full transition-all duration-500"
                             :class="currentSkuIndex === i ? 'w-8 bg-primary shadow-[0_0_10px_rgba(var(--primary),0.5)]' : 'w-1.5 bg-gray-100 dark:bg-[#262626]'"></div>
                    </div>
                </div>

                <div class="p-6 bg-[#FFFFFF] dark:bg-[#050505] border-t border-gray-100 dark:border-[#262626] space-y-5">
                    
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col">
                            <span class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Precio Unitario</span>
                            <div class="flex items-baseline gap-1">
                                <span class="text-sm font-semibold text-gray-400">Bs</span>
                                <span class="text-4xl font-bold tracking-[-0.02em] text-gray-900 dark:text-white">
                                    {{ currentPriceData.price.toFixed(2) }}
                                </span>
                            </div>
                        </div>

                        <div v-if="currentPriceData.next_tier" class="bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-100 dark:border-emerald-500/20 px-3 py-2 rounded-lg flex items-center gap-2 animate-pulse">
                            <Zap :size="12" class="text-emerald-500 fill-current" />
                            <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-tighter">
                                +{{ currentPriceData.next_tier.min_qty - quantity }} para bajar a Bs {{ currentPriceData.next_tier.price.toFixed(2) }}
                            </span>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <div class="flex-1 flex items-center justify-between bg-gray-50 dark:bg-[#121217] rounded-lg p-1 border border-gray-100 dark:border-[#262626]">
                            <button @click="updateQty(-1)" class="w-11 h-11 flex items-center justify-center text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors" :disabled="quantity <= 1">
                                <Minus :size="18" stroke-width="2.5" />
                            </button>
                            <span class="text-lg font-bold text-gray-900 dark:text-white">{{ quantity }}</span>
                            <button @click="updateQty(1)" class="w-11 h-11 flex items-center justify-center text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors" :disabled="quantity >= (activeSku?.available_stock || 1)">
                                <Plus :size="18" stroke-width="2.5" />
                            </button>
                        </div>

                        <button @click="addToCart" 
                                :disabled="!activeSku || activeSku.available_stock <= 0 || isSubmitting"
                                class="flex-[2] bg-primary text-white font-bold uppercase tracking-wider text-xs rounded-lg flex items-center justify-center gap-3 transition-all active:scale-[0.98] disabled:opacity-40 shadow-sm dark:shadow-[0_0_20px_rgba(var(--primary),0.4)]">
                            <Loader2 v-if="isSubmitting" class="animate-spin" :size="18" />
                            <template v-else>
                                <ShoppingCart :size="18" stroke-width="2.5" />
                                {{ activeSku?.available_stock > 0 ? 'Añadir al Pedido' : 'Sin Stock' }}
                            </template>
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.slide-up-enter-active { transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1); }
.slide-up-leave-active { transition: all 0.4s cubic-bezier(0.7, 0, 0.84, 0); }
.slide-up-enter-from, .slide-up-leave-to { transform: translateY(100%); }
</style>