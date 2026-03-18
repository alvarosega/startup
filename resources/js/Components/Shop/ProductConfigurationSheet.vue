<script setup>
import { ref, computed, watch, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import { X, ShoppingCart, Plus, Minus, PackageX, Loader2, Zap } from 'lucide-vue-next';

const props = defineProps({
    product: {
        type: Object,
        default: null
    }
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
        const availableSkus = [...newProduct.skus].sort((a, b) => a.price - b.price);
        const startIndex = newProduct.skus.findIndex(s => s.id === availableSkus[0].id);
        
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

const currentPriceData = computed(() => {
    return getSkuPriceData(activeSku.value, quantity.value);
});

const scrollToIndex = (index, behavior = 'smooth') => {
    if (!carouselRef.value) return;
    const card = carouselRef.value.children[index];
    if (card) {
        card.scrollIntoView({ behavior, block: 'nearest', inline: 'center' });
        if (currentSkuIndex.value !== index) {
            currentSkuIndex.value = index;
            quantity.value = 1; 
        }
    }
};

const onCarouselScroll = () => {
    if (!carouselRef.value || !props.product) return;
    
    const scrollLeft = carouselRef.value.scrollLeft;
    const containerWidth = carouselRef.value.getBoundingClientRect().width;
    const cardWidth = containerWidth * 0.70; 
    
    const newIndex = Math.round(scrollLeft / cardWidth);
    
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

const close = () => {
    emit('close');
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
            close();
        },
        onError: () => {
            isSubmitting.value = false;
        }
    });
};
</script>

<template>
    <div>
        <Transition name="fade">
            <div v-if="product" @click="close" class="fixed inset-0 z-[90] bg-black/40 dark:bg-black/80 backdrop-blur-sm"></div>
        </Transition>

        <Transition name="slide-up">
            <div v-if="product" class="fixed inset-x-0 bottom-0 z-[100] bg-[#FFFFFF] dark:bg-[#050505] rounded-t-xl dark:border-t dark:border-[#262626] shadow-[0_-10px_40px_rgba(0,0,0,0.08)] dark:shadow-none flex flex-col h-[85vh] md:inset-0 md:m-auto md:w-full md:max-w-[420px] md:h-fit md:max-h-[85vh] md:rounded-xl md:border md:border-transparent md:dark:border-[#262626]">
                
                <div class="shrink-0 pt-4 pb-3 flex flex-col items-center relative border-b border-gray-100 dark:border-[#262626]">
                    <div @click="close" class="w-12 h-1.5 bg-gray-200 dark:bg-[#262626] rounded-full mb-3 cursor-pointer md:hidden"></div>
                    <h3 class="text-base font-bold tracking-[-0.02em] text-gray-900 dark:text-white leading-none truncate px-12 text-center w-full md:mt-2 md:mb-1">
                        {{ product.name }}
                    </h3>
                    <button @click="close" class="absolute top-4 right-4 w-8 h-8 flex items-center justify-center rounded-lg bg-gray-50 dark:bg-[#121217] border border-transparent dark:border-[#262626] active:scale-95 transition-transform">
                        <X class="w-4 text-gray-900 dark:text-white" stroke-width="2.5" />
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto custom-scrollbar flex flex-col justify-center py-4">
                    
                    <div ref="carouselRef" @scroll="onCarouselScroll" class="flex overflow-x-auto snap-x snap-mandatory no-scrollbar w-full items-center shrink-0" style="padding-left: 15%; padding-right: 15%;">
                        <div v-for="(sku, index) in product.skus" :key="sku.id"
                             @click="scrollToIndex(index)"
                             class="shrink-0 snap-center flex justify-center items-center transition-all duration-300 ease-out cursor-pointer relative aspect-square rounded-xl bg-[#FFFFFF] dark:bg-[#121217] border border-transparent dark:border-[#262626]"
                             style="width: 70%;"
                             :class="[
                                currentSkuIndex === index ? 'scale-100 opacity-100 shadow-sm dark:shadow-none' : 'scale-[0.85] opacity-40 grayscale-[30%]',
                                {'dark:hover:border-[#E10600]/50': currentSkuIndex !== index}
                             ]">
                            <img :src="getImageUrl(sku.image_url)" class="w-full h-full object-contain p-4 transition-transform duration-300" :class="currentSkuIndex === index ? 'scale-105 drop-shadow-md dark:drop-shadow-none' : 'scale-100'">
                        </div>
                    </div>

                    <div class="flex justify-center gap-2 mt-4 mb-4 shrink-0">
                        <button v-for="(sku, index) in product.skus" :key="'ind-'+sku.id"
                                @click="scrollToIndex(index)"
                                class="w-4 h-4 rounded-lg border flex items-center justify-center transition-all duration-300"
                                :class="currentSkuIndex === index ? 'border-[#007AFF] bg-[#007AFF]/10 dark:border-[#E10600] dark:bg-[#E10600]/10 dark:shadow-[0_0_10px_rgba(225,6,0,0.5)]' : 'border-gray-200 bg-gray-50 dark:border-[#262626] dark:bg-[#121217]'">
                            <div v-if="currentSkuIndex === index" class="w-1.5 h-1.5 rounded-sm bg-[#007AFF] dark:bg-[#E10600]"></div>
                        </button>
                    </div>

                    <div v-if="activeSku" class="px-4 flex flex-col items-center text-center shrink-0">
                        <h4 class="text-lg font-bold tracking-[-0.02em] text-gray-900 dark:text-white leading-tight mb-2">{{ activeSku.name }}</h4>
                        
                        <div class="flex items-baseline gap-1 mb-3">
                            <span class="text-sm font-semibold text-gray-500 dark:text-gray-400">Bs</span>
                            <span class="text-4xl font-semibold text-gray-900 dark:text-white leading-none transition-colors duration-300">
                                {{ currentPriceData.price.toFixed(2) }}
                            </span>
                        </div>

                        <div v-if="currentPriceData.next_tier" class="inline-flex items-center gap-1.5 text-[11px] font-bold tracking-[-0.02em] text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-400/10 py-1.5 px-3 rounded-lg border border-transparent dark:border-emerald-400/20 dark:shadow-[0_0_10px_rgba(16,185,129,0.3)]">
                            <Zap :size="12" class="fill-current" />
                            AÑADE {{ currentPriceData.next_tier.min_qty - quantity }} MÁS PARA BAJAR A BS {{ currentPriceData.next_tier.price.toFixed(2) }}
                        </div>
                    </div>
                </div>

                <div class="shrink-0 p-4 bg-[#FFFFFF] dark:bg-[#050505] border-t border-gray-100 dark:border-[#262626] md:rounded-b-xl">
                    <div class="flex flex-col gap-4">
                        
                        <div class="flex items-center justify-between bg-gray-50 dark:bg-[#121217] border border-gray-200 dark:border-[#262626] rounded-lg p-1 h-12">
                            <button @click="updateQty(-1)" class="w-12 h-full flex items-center justify-center text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white active:scale-95 disabled:opacity-30 transition-transform" :disabled="!activeSku || activeSku.available_stock <= 0 || quantity <= 1">
                                <Minus :size="18" stroke-width="2.5" />
                            </button>
                            
                            <span class="text-xl font-semibold text-gray-900 dark:text-white w-16 text-center" :class="{'text-gray-300 dark:text-[#262626]': !activeSku || activeSku.available_stock <= 0}">
                                {{ activeSku && activeSku.available_stock > 0 ? quantity : 0 }}
                            </span>
                            
                            <button @click="updateQty(1)" class="w-12 h-full flex items-center justify-center text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white active:scale-95 disabled:opacity-30 transition-transform" :disabled="!activeSku || activeSku.available_stock <= 0 || quantity >= activeSku.available_stock">
                                <Plus :size="18" stroke-width="2.5" />
                            </button>
                        </div>

                        <button @click="addToCart" :disabled="!activeSku || activeSku.available_stock <= 0 || isSubmitting" class="w-full h-12 bg-[#007AFF] dark:bg-[#E10600] text-white font-semibold tracking-[-0.02em] rounded-lg flex items-center justify-center gap-2 active:scale-[0.98] transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-400 dark:disabled:bg-[#121217] dark:disabled:border dark:disabled:border-[#262626] shadow-sm dark:shadow-[0_0_15px_rgba(225,6,0,0.5)] dark:hover:shadow-[0_0_25px_rgba(225,6,0,0.8)] disabled:shadow-none">
                            <template v-if="isSubmitting">
                                <Loader2 class="animate-spin" :size="18" stroke-width="2.5" />
                                Procesando
                            </template>
                            <template v-else-if="activeSku && activeSku.available_stock <= 0">
                                <PackageX :size="18" stroke-width="2.5" />
                                Sin Stock
                            </template>
                            <template v-else>
                                <ShoppingCart :size="18" stroke-width="2.5" />
                                Añadir Variante
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
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(150, 150, 150, 0.2); border-radius: 8px; }
.slide-up-enter-active, .slide-up-leave-active { transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
.slide-up-enter-from, .slide-up-leave-to { transform: translateY(100%); }
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>