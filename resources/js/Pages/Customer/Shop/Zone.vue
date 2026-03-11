<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import { ArrowLeft, Plus, Minus, PackageX, X, ShoppingCart, ChevronLeft, ChevronRight, Star, Heart } from 'lucide-vue-next';

const props = defineProps({
    zone: Object,
    groupedCategories: { type: Array, default: () => [] },
    targetCategory: [String, Number, null]
});
const page = usePage();
const toggleFavorite = (productId, event) => {
    event.stopPropagation();
    router.post(route('customer.favorites.toggle', productId), {}, {
        preserveScroll: true,
        preserveState: true
    });
};
// --- MOTOR DE RESOLUCIÓN DE PRECIOS (Refactor v2.1) ---
const currentPriceData = computed(() => {
    // 1. Guardián de seguridad: Si no hay SKU seleccionado, devolvemos estado neutro
    if (!selectedSku.value) {
        return { price: 0, list_price: 0, type: 'regular', next_tier: null };
    }

    const sku = selectedSku.value;
    const qty = quantity.value;
    
    // 2. Si el backend aún no ha procesado 'all_prices', usamos los valores base del SKU
    if (!sku.all_prices || !Array.isArray(sku.all_prices)) {
        return { 
            price: sku.price || 0, 
            list_price: sku.list_price || 0, 
            type: 'regular', 
            next_tier: null 
        };
    }

    // 3. Resolución del "Winning Price" (Prioridad > Cantidad)
    // Clonamos para no mutar el prop original
    const winning = [...sku.all_prices]
        .filter(p => qty >= p.min_quantity)
        .sort((a, b) => b.priority - a.priority || b.min_quantity - a.min_quantity)[0];

    // 4. Resolución del "Next Tier" (Upsell)
    const next = [...sku.all_prices]
        .filter(p => p.min_quantity > qty && p.final_price < (winning?.final_price || sku.price))
        .sort((a, b) => a.min_quantity - b.min_quantity)[0];

    return {
        price: winning ? winning.final_price : sku.price,
        list_price: winning ? winning.list_price : sku.list_price,
        type: winning ? winning.type : 'regular',
        next_tier: next ? {
            min_qty: next.min_quantity,
            price: next.final_price
        } : null
    };
});
// --- ESTADO Y MOTOR DE SCROLL SPY ---
const activeTab = ref(props.groupedCategories.length > 0 ? props.groupedCategories[0].id : null);
const isScrollingProgrammatically = ref(false);
let observer = null;

const getGuestUuid = () => {
    let uuid = localStorage.getItem('guest_client_uuid');
    if (!uuid) {
        uuid = crypto.randomUUID();
        localStorage.setItem('guest_client_uuid', uuid);
    }
    return uuid;
};

onMounted(() => {
    observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && !isScrollingProgrammatically.value) {
                const categoryId = entry.target.getAttribute('data-category-id');
                activeTab.value = isNaN(categoryId) ? categoryId : Number(categoryId);
                
                const activeTabEl = document.getElementById(`tab-${categoryId}`);
                if (activeTabEl) {
                    activeTabEl.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                }
            }
        });
    }, {
        root: null,
        rootMargin: '-150px 0px -60% 0px', 
        threshold: 0
    });

    document.querySelectorAll('.category-section').forEach(el => observer.observe(el));

    if (props.targetCategory) {
        nextTick(() => scrollToCategory(props.targetCategory));
    }
});

onUnmounted(() => {
    if (observer) observer.disconnect();
});

const scrollToCategory = (id) => {
    activeTab.value = id;
    isScrollingProgrammatically.value = true;
    
    const element = document.getElementById(`category-block-${id}`);
    if (element) {
        const y = element.getBoundingClientRect().top + window.scrollY - 150;
        window.scrollTo({ top: y, behavior: 'smooth' });
    }
    setTimeout(() => { isScrollingProgrammatically.value = false; }, 800);
};



// --- ESTADO DEL BOTTOM SHEET (ESCAPARATE DE SKUS) ---
const isBottomSheetOpen = ref(false);
// --- ESTADO DEL MODAL DE RESEÑAS ---
const isReviewModalOpen = ref(false);
const reviewForm = ref({
    rating: 0,
    comment: ''
});
const hoverRating = ref(0);
const reviewProductTarget = ref(null);
const isSubmittingReview = ref(false);

const openReviewModal = (product, event) => {
    event.stopPropagation();
    // Validar Zero-Trust: Solo usuarios registrados
    if (!page.props.auth?.user) {
        // Podrías lanzar un Toast aquí en lugar de redirigir bruscamente si prefieres
        router.visit(route('login'));
        return;
    }
    
    reviewProductTarget.value = product;
    reviewForm.value = { rating: 0, comment: '' };
    hoverRating.value = 0;
    isReviewModalOpen.value = true;
};

const closeReviewModal = () => {
    isReviewModalOpen.value = false;
    setTimeout(() => {
        reviewProductTarget.value = null;
    }, 300);
};

const setRating = (val) => reviewForm.value.rating = val;

const submitReview = () => {
    if (reviewForm.value.rating < 1 || reviewForm.value.rating > 5) return;
    
    isSubmittingReview.value = true;
    router.post(route('customer.reviews.store', reviewProductTarget.value.id), reviewForm.value, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            closeReviewModal();
            isSubmittingReview.value = false;
        },
        onError: () => {
            isSubmittingReview.value = false;
        }
    });
};
const selectedProduct = ref(null);
const selectedSku = ref(null);
const quantity = ref(1);

const carouselRef = ref(null);
const currentSkuIndex = ref(0);

const openBottomSheet = (product) => {
    selectedProduct.value = product;
    const availableIndex = product.skus.findIndex(sku => sku.available_stock > 0);
    currentSkuIndex.value = availableIndex !== -1 ? availableIndex : 0;
    selectedSku.value = product.skus[currentSkuIndex.value];
    quantity.value = 1;
    isBottomSheetOpen.value = true;

    nextTick(() => scrollToIndex(currentSkuIndex.value, 'instant'));
};

const closeBottomSheet = () => {
    isBottomSheetOpen.value = false;
    setTimeout(() => {
        selectedProduct.value = null;
        selectedSku.value = null;
        currentSkuIndex.value = 0;
    }, 300);
};

const prevSku = () => {
    if (currentSkuIndex.value > 0) {
        currentSkuIndex.value--;
        scrollToIndex(currentSkuIndex.value, 'smooth');
    }
};

const nextSku = () => {
    if (selectedProduct.value && currentSkuIndex.value < selectedProduct.value.skus.length - 1) {
        currentSkuIndex.value++;
        scrollToIndex(currentSkuIndex.value, 'smooth');
    }
};

const scrollToIndex = (index, behavior = 'smooth') => {
    if (!carouselRef.value) return;
    const card = carouselRef.value.children[index];
    if (card) {
        card.scrollIntoView({ behavior, block: 'nearest', inline: 'center' });
        selectedSku.value = selectedProduct.value.skus[index];
        quantity.value = 1;
    }
};

const onCarouselScroll = () => {
    if (!carouselRef.value || !selectedProduct.value) return;
    const scrollLeft = carouselRef.value.scrollLeft;
    const cardWidth = window.innerWidth * 0.8; 
    const newIndex = Math.round(scrollLeft / cardWidth);
    
    if (newIndex !== currentSkuIndex.value && newIndex >= 0 && newIndex < selectedProduct.value.skus.length) {
        currentSkuIndex.value = newIndex;
        selectedSku.value = selectedProduct.value.skus[newIndex];
        quantity.value = 1;
    }
};

const increaseQty = () => { if (selectedSku.value && quantity.value < selectedSku.value.available_stock) quantity.value++; };
const decreaseQty = () => { if (quantity.value > 1) quantity.value--; };

const addToCart = () => {
    if (!selectedSku.value || selectedSku.value.available_stock <= 0) return;
    router.post(route('customer.cart.add'), { 
        sku_id: selectedSku.value.id,
        quantity: quantity.value,
        guest_client_uuid: getGuestUuid()
    }, {
        preserveScroll: true,
        onSuccess: () => closeBottomSheet()
    });
};

const goBack = () => router.visit(route('customer.shop.index'));

const getImageUrl = (path) => {
    if (!path) return '/assets/img/placeholder.png';
    if (path.startsWith('http')) return path;
    return `/storage/${path.replace(/^\/+/, '')}`;
};
</script>

<template>
    <ShopLayout>
        <Head :title="zone?.name || 'Zona'" />
        
        <div class="min-h-screen bg-transparent pb-32 relative">
            
            <header class="sticky top-[64px] z-50 bg-background/40 backdrop-blur-xl border-b border-white/10 dark:border-white/5 px-4 py-3">
                <div class="container mx-auto flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <button @click="goBack" class="w-10 h-10 flex items-center justify-center rounded-full bg-white/5 border border-white/10 transition-transform active:scale-90">
                            <ArrowLeft class="w-5 h-5 text-foreground" stroke-width="2.5" />
                        </button>
                        <h1 class="font-black text-xl uppercase tracking-tighter text-foreground leading-none">{{ zone?.name }}</h1>
                    </div>
                </div>
            </header>

            <div class="sticky top-[128px] z-40 w-full bg-background/40 backdrop-blur-xl border-b border-white/10 pt-3 overflow-x-auto no-scrollbar shadow-sm scroll-smooth">
                <div class="flex px-4 gap-2">
                    <button v-for="parent in groupedCategories" :key="parent.id"
                            :id="`tab-${parent.id}`"
                            @click="scrollToCategory(parent.id)"
                            class="px-5 py-2.5 rounded-t-[16px] text-[11px] font-black uppercase transition-all duration-300 relative whitespace-nowrap"
                            :class="activeTab === parent.id 
                                ? 'bg-white/10 dark:bg-black/20 text-foreground border-t border-x border-white/20 dark:border-white/10 z-10' 
                                : 'bg-transparent text-foreground/50 border-t border-x border-transparent hover:text-foreground mt-1'">
                        {{ parent.name }}
                    </button>
                </div>
            </div>

            <div class="container mx-auto py-6 px-4 space-y-12 relative z-0">
                <div v-for="parent in groupedCategories" :key="parent.id" 
                     :id="`category-block-${parent.id}`"
                     :data-category-id="parent.id"
                     class="category-section pt-4 animate-fade-in"> 
                    
                    <h2 class="text-2xl font-black uppercase mb-6 text-foreground tracking-tighter border-l-4 border-primary pl-3 leading-none">
                        {{ parent.name }}
                    </h2>
                    
                    <div v-for="category in parent.categories" :key="category.id" :id="`category-${category.id}`" class="mb-10">
                        
                        <h3 class="text-[11px] font-black uppercase tracking-widest mb-4 flex items-center gap-2"
                            :style="{ color: category.bg_color || 'rgba(var(--foreground), 0.6)' }">
                            <span class="w-1.5 h-1.5 rounded-full" :style="{ backgroundColor: category.bg_color || 'rgba(var(--foreground), 0.3)' }"></span> 
                            <span class="text-foreground/80">{{ category.name }}</span>
                        </h3>
                        
                        <div class="grid grid-cols-2 gap-3">
                            <div v-for="product in category.products" :key="product.id" 
                                 @click="openBottomSheet(product)"
                                 :style="{ borderColor: category.bg_color ? `${category.bg_color}50` : 'rgba(255,255,255,0.1)' }"
                                 class="bg-white/5 dark:bg-black/20 backdrop-blur-xl rounded-[24px] border p-3 pb-4 relative flex flex-col group transition-transform active:scale-95 overflow-hidden shadow-sm cursor-pointer hover:shadow-lg"
                                 :class="{'border-white/10 dark:border-white/5': !category.bg_color}">
                                
                                <div v-if="product.available_stock <= 0" class="absolute inset-0 z-30 bg-black/40 backdrop-blur-sm flex items-center justify-center rounded-[24px]">
                                    <span class="bg-foreground text-background text-[9px] font-black uppercase tracking-widest px-3 py-1.5 rounded-md shadow-xl">Agotado</span>
                                </div>

                                <button v-if="$page.props.auth?.user" 
                                        @click="(e) => toggleFavorite(product.id, e)" 
                                        class="absolute top-3 right-3 z-20 w-8 h-8 rounded-full bg-background/40 backdrop-blur-md flex items-center justify-center border border-white/10 shadow-inner hover:scale-110 transition-transform">
                                    <Heart :size="14" 
                                           :class="product.is_favorited ? 'text-f1-red fill-f1-red' : 'text-foreground/50'" 
                                           stroke-width="2.5" />
                                </button>

                                <div class="relative z-10 w-full h-28 flex items-center justify-center mb-3 mt-4 pointer-events-none">
                                    <div class="absolute inset-0 blur-[25px] rounded-full opacity-30" 
                                         :style="{ backgroundColor: category.bg_color || 'rgba(var(--primary), 0.5)' }"></div>
                                    <img :src="getImageUrl(product.image_url)" class="relative z-10 w-full h-full object-contain drop-shadow-[0_10px_15px_rgba(0,0,0,0.4)]">
                                </div>
                                
                                <div class="relative z-20 flex flex-col flex-1 justify-end px-1">
                                    <h4 class="text-[11px] font-sans font-black uppercase text-foreground leading-tight line-clamp-2 mb-1 drop-shadow-md">
                                        {{ product.name }}
                                    </h4>                                    


                                    <div @click="(e) => openReviewModal(product, e)" 
                                         class="flex items-center gap-0.5 mb-3 cursor-pointer group/star active:scale-95 transition-transform">
                                        <Star v-for="i in 5" :key="i" :size="9" 
                                              :class="i <= Math.round(product.reviews_avg_rating || 0) ? 'text-warning fill-warning' : 'text-foreground/20 fill-foreground/10'" />
                                        <span class="text-[8px] font-bold text-foreground/40 ml-1 mt-0.5">({{ product.reviews_count }})</span>
                                    </div>
                                    
                                    <div class="mt-auto pt-2 border-t border-white/5 flex items-center justify-between">
                                        <div class="text-[9px] font-black uppercase tracking-widest flex items-center gap-1 transition-colors"
                                             :style="{ color: category.bg_color || 'rgb(var(--primary))' }">
                                            Ver Opciones <ChevronRight :size="10" stroke-width="3" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <Transition name="fade">
            <div v-if="isBottomSheetOpen" @click="closeBottomSheet" class="fixed inset-0 z-[60] bg-background/80 backdrop-blur-sm"></div>
        </Transition>

        <Transition name="slide-up">
            <div v-if="isBottomSheetOpen" class="fixed inset-x-0 bottom-0 z-[70] bg-surface/80 backdrop-blur-3xl border-t border-white/10 rounded-t-[40px] pt-6 pb-8 shadow-[0_-20px_50px_rgba(0,0,0,0.5)] flex flex-col max-h-[95vh]">
                
                <div class="w-12 h-1.5 bg-foreground/20 rounded-full mx-auto mb-6 shrink-0"></div>
                
                <div class="px-6 relative mb-6">
                    <button @click="closeBottomSheet" class="absolute left-6 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-white/5 flex items-center justify-center border border-white/10 active:scale-90 transition-transform"><X :size="18" stroke-width="3" class="text-foreground" /></button>
                    <h3 class="text-[11px] font-black text-center uppercase text-foreground/60 tracking-widest truncate px-12">{{ selectedProduct?.name }}</h3>
                </div>

                <div class="relative w-full mb-6">
                    <button v-show="currentSkuIndex > 0" @click="prevSku" class="absolute left-4 top-[40%] -translate-y-1/2 z-20 w-10 h-10 rounded-full bg-background/60 backdrop-blur border border-white/10 flex items-center justify-center text-foreground shadow-lg transition-transform active:scale-90">
                        <ChevronLeft :size="20" strokeWidth="3" />
                    </button>
                    <button v-show="selectedProduct && currentSkuIndex < selectedProduct.skus.length - 1" @click="nextSku" class="absolute right-4 top-[40%] -translate-y-1/2 z-20 w-10 h-10 rounded-full bg-background/60 backdrop-blur border border-white/10 flex items-center justify-center text-foreground shadow-lg transition-transform active:scale-90">
                        <ChevronRight :size="20" strokeWidth="3" />
                    </button>

                    <div ref="carouselRef" @scroll="onCarouselScroll" class="flex overflow-x-auto snap-x snap-mandatory no-scrollbar w-full px-[10vw] gap-[3vw] py-4">
                        
                        <div v-for="(sku, index) in selectedProduct?.skus" :key="sku.id"
                             class="w-[80vw] shrink-0 snap-center rounded-[32px] bg-white/5 dark:bg-black/20 border border-white/10 flex flex-col items-center p-8 relative shadow-inner transition-all duration-500 overflow-hidden spread-card-anim"
                             :style="{ '--delay': `${index * 0.1}s` }"
                             :class="sku.available_stock <= 0 ? 'opacity-50 grayscale' : (currentSkuIndex === index ? 'border-primary/50 shadow-[0_0_30px_rgba(var(--primary),0.1)] scale-100' : 'scale-[0.95] opacity-60')">
                            
                            <div v-if="sku.available_stock <= 0" class="absolute top-5 left-5 z-20 bg-foreground text-background text-[10px] font-black uppercase tracking-widest px-3 py-1.5 rounded-lg shadow-md">Agotado</div>

                            <img :src="getImageUrl(sku.image_url)" class="relative z-10 w-48 h-48 object-contain drop-shadow-[0_20px_25px_rgba(0,0,0,0.4)] mb-8 transition-transform duration-500">
                            
                            <h4 class="relative z-10 text-xl font-sans font-black uppercase text-foreground leading-none text-center mb-3 drop-shadow-md">{{ sku.name }}</h4>

                            <div class="relative z-10 flex flex-col items-center gap-2 mb-4 w-full">
                                <div class="flex items-baseline gap-3">
                                    <span v-if="currentPriceData.list_price > currentPriceData.price" 
                                        class="text-sm font-mono font-bold text-foreground/30 line-through">
                                        BS. {{ currentPriceData.list_price }}
                                    </span>
                                    
                                    <div class="text-4xl font-mono font-black text-primary drop-shadow-lg transition-all duration-300"
                                        :class="{'scale-110 text-warning': currentPriceData.type !== 'regular'}">
                                        <span class="text-sm text-foreground/50 mr-1 tracking-widest">BS.</span>{{ currentPriceData.price }}
                                    </div>
                                </div>
                                
                                <div v-if="currentPriceData.type !== 'regular'" class="mb-1">
                                    <span class="text-[8px] font-black px-2 py-0.5 rounded bg-primary text-white uppercase tracking-widest">
                                        Precio {{ currentPriceData.type }}
                                    </span>
                                </div>

                                <div v-if="currentPriceData.list_price > currentPriceData.price" 
                                    class="bg-emerald-500/10 border border-emerald-500/20 px-4 py-1.5 rounded-full shadow-sm">
                                    <span class="text-[10px] font-black text-emerald-500 uppercase tracking-tighter">
                                        Ahorras BS. {{ ((currentPriceData.list_price - currentPriceData.price) * quantity).toFixed(2) }} en este pedido
                                    </span>
                                </div>

                                <div v-if="currentPriceData.next_tier" 
                                    class="bg-primary/5 border border-primary/10 px-4 py-2 rounded-xl mt-1 max-w-[80%] text-center animate-pulse-subtle">
                                    <p class="text-[9px] font-bold text-foreground/60 uppercase leading-tight">
                                        ¡Agrega <span class="text-primary font-black">{{ currentPriceData.next_tier.min_qty - quantity }}</span> más para pagar solo 
                                        <span class="text-primary font-black">BS. {{ currentPriceData.next_tier.price }}</span> c/u!
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-6">
                    <div class="flex items-center gap-4 mb-4 bg-background/50 border border-white/10 rounded-[20px] p-2 shadow-inner">
                        <button @click="decreaseQty" class="w-14 h-14 rounded-[16px] bg-white/5 flex items-center justify-center text-foreground hover:bg-white/10 transition-colors active:scale-95" :disabled="quantity <= 1 || selectedSku?.available_stock <= 0"><Minus :size="24" strokeWidth="2.5" /></button>
                        
                        <div class="flex-1 flex flex-col items-center justify-center">
                            <span class="font-mono font-black text-3xl text-foreground leading-none" :class="selectedSku?.available_stock <= 0 ? 'text-foreground/30' : ''">{{ selectedSku?.available_stock > 0 ? quantity : 0 }}</span>
                            <span v-if="selectedSku?.available_stock <= 5 && selectedSku?.available_stock > 0" class="text-[9px] font-black text-warning uppercase tracking-widest mt-1">
                                Quedan {{ selectedSku.available_stock }}
                            </span>
                        </div>
                        
                        <button @click="increaseQty" class="w-14 h-14 rounded-[16px] bg-white/5 flex items-center justify-center text-foreground hover:bg-white/10 transition-colors active:scale-95" :disabled="!selectedSku || quantity >= selectedSku.available_stock || selectedSku.available_stock <= 0"><Plus :size="24" strokeWidth="2.5" /></button>
                    </div>
                    
                    <button @click="addToCart" 
                            class="w-full h-16 font-sans font-black text-sm uppercase tracking-widest rounded-[20px] flex items-center justify-center gap-3 transition-all duration-300 active:scale-95"
                            :class="selectedSku?.available_stock > 0 
                                ? 'bg-primary text-white shadow-xl shadow-primary/30' 
                                : 'bg-white/5 text-foreground/30 border border-white/10 cursor-not-allowed'">
                        <ShoppingCart v-if="selectedSku?.available_stock > 0" :size="20" stroke-width="2.5" />
                        <PackageX v-else :size="20" stroke-width="2.5" />
                        {{ selectedSku?.available_stock > 0 ? 'Agregar al Pedido' : 'Sin Existencias' }}
                    </button>
                </div>

            </div>
            
        </Transition>
        <Transition name="fade">
            <div v-if="isReviewModalOpen" @click="closeReviewModal" class="fixed inset-0 z-[80] bg-background/80 backdrop-blur-sm"></div>
        </Transition>

        <Transition name="slide-up">
            <div v-if="isReviewModalOpen" class="fixed inset-x-0 bottom-0 z-[90] bg-surface/90 backdrop-blur-3xl border-t border-white/10 rounded-t-[40px] pt-6 pb-8 shadow-[0_-20px_50px_rgba(0,0,0,0.5)] flex flex-col">
                
                <div class="w-12 h-1.5 bg-foreground/20 rounded-full mx-auto mb-6 shrink-0"></div>
                
                <div class="px-6 relative mb-4">
                    <button @click="closeReviewModal" class="absolute left-6 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-white/5 flex items-center justify-center border border-white/10 active:scale-90 transition-transform">
                        <X :size="18" stroke-width="3" class="text-foreground" />
                    </button>
                    <h3 class="text-[11px] font-black text-center uppercase text-foreground/60 tracking-widest truncate px-12">Calificar Producto</h3>
                </div>

                <div class="px-8 flex flex-col items-center">
                    <img :src="getImageUrl(reviewProductTarget?.image_url)" class="w-24 h-24 object-contain drop-shadow-[0_10px_15px_rgba(0,0,0,0.4)] mb-4">
                    <h4 class="text-sm font-sans font-black uppercase text-foreground leading-tight text-center mb-8">{{ reviewProductTarget?.name }}</h4>

                    <div class="flex items-center gap-2 mb-8">
                        <button v-for="star in 5" :key="star"
                                @click="setRating(star)"
                                @mouseenter="hoverRating = star"
                                @mouseleave="hoverRating = 0"
                                class="w-12 h-12 rounded-full flex items-center justify-center transition-all active:scale-90"
                                :class="(hoverRating ? star <= hoverRating : star <= reviewForm.rating) ? 'bg-warning/20 border border-warning/50' : 'bg-white/5 border border-white/10'">
                            <Star :size="24" 
                                  class="transition-colors"
                                  :class="(hoverRating ? star <= hoverRating : star <= reviewForm.rating) ? 'text-warning fill-warning' : 'text-foreground/30'" />
                        </button>
                    </div>

                    <div class="w-full mb-6">
                        <textarea v-model="reviewForm.comment" 
                                  placeholder="¿Qué te pareció este producto? (Opcional)"
                                  rows="3"
                                  maxlength="500"
                                  class="w-full bg-background/50 backdrop-blur-md border border-white/10 rounded-[20px] p-4 text-xs font-bold text-foreground placeholder:text-foreground/30 focus:border-primary focus:ring-0 resize-none"></textarea>
                    </div>

                    <button @click="submitReview"
                            :disabled="reviewForm.rating === 0 || isSubmittingReview"
                            class="w-full h-14 font-sans font-black text-sm uppercase tracking-widest rounded-[20px] flex items-center justify-center gap-3 transition-all duration-300 active:scale-95"
                            :class="reviewForm.rating > 0 && !isSubmittingReview
                                ? 'bg-primary text-white shadow-xl shadow-primary/30' 
                                : 'bg-white/5 text-foreground/30 border border-white/10 cursor-not-allowed'">
                        <span v-if="isSubmittingReview" class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                        <template v-else>
                            Enviar Reseña
                        </template>
                    </button>
                </div>
            </div>
        </Transition>
    </ShopLayout>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

.slide-up-enter-active, .slide-up-leave-active { transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.4s ease; }
.slide-up-enter-from, .slide-up-leave-to { transform: translateY(100%); opacity: 0; }
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
.animate-fade-in { animation: fadeIn 0.4s ease-out forwards; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

.spread-card-anim { animation: spreadOut 0.5s cubic-bezier(0.16, 1, 0.3, 1) both; animation-delay: var(--delay); }
@keyframes spreadOut { 0% { opacity: 0; transform: scale(0.8) translateY(30px); } 100% { opacity: 1; transform: scale(1) translateY(0); } }

@keyframes pulse-subtle {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.8; transform: scale(0.98); }
}
.animate-pulse-subtle {
    animation: pulse-subtle 3s infinite ease-in-out;
}
</style>
