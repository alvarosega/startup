<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import { ArrowLeft, Plus, Minus, PackageX, X, ShoppingCart, ChevronLeft, ChevronRight } from 'lucide-vue-next';

const props = defineProps({
    zone: Object,
    groupedCategories: { type: Array, default: () => [] },
    targetCategory: [String, Number, null]
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
const selectedProduct = ref(null);
const selectedSku = ref(null);
const quantity = ref(1);

// Telemetría del Carrusel de SKUs
const carouselRef = ref(null);
const currentSkuIndex = ref(0);

const openBottomSheet = (product) => {
    selectedProduct.value = product;
    
    // Inicia en el primer SKU con stock, o el primero por defecto
    const availableIndex = product.skus.findIndex(sku => sku.available_stock > 0);
    currentSkuIndex.value = availableIndex !== -1 ? availableIndex : 0;
    selectedSku.value = product.skus[currentSkuIndex.value];
    
    quantity.value = 1;
    isBottomSheetOpen.value = true;

    // Desplazar el carrusel al SKU inicial tras renderizar el DOM
    nextTick(() => {
        scrollToIndex(currentSkuIndex.value, 'instant');
    });
};

const closeBottomSheet = () => {
    isBottomSheetOpen.value = false;
    setTimeout(() => {
        selectedProduct.value = null;
        selectedSku.value = null;
        currentSkuIndex.value = 0;
    }, 300);
};

// Navegación Manual (Flechas)
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
        quantity.value = 1; // Reset de cantidad por seguridad de stock
    }
};

// Sincronización al usar Swipe Táctil
const onCarouselScroll = () => {
    if (!carouselRef.value || !selectedProduct.value) return;
    const scrollLeft = carouselRef.value.scrollLeft;
    // Ancho matemático: 80vw + gap (asumimos que el snap hace el trabajo fuerte)
    const cardWidth = window.innerWidth * 0.8; 
    const newIndex = Math.round(scrollLeft / cardWidth);
    
    if (newIndex !== currentSkuIndex.value && newIndex >= 0 && newIndex < selectedProduct.value.skus.length) {
        currentSkuIndex.value = newIndex;
        selectedSku.value = selectedProduct.value.skus[newIndex];
        quantity.value = 1;
    }
};

// Controles de Carrito
const increaseQty = () => {
    if (selectedSku.value && quantity.value < selectedSku.value.available_stock) {
        quantity.value++;
    }
};
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
        
        <div class="min-h-screen bg-background pb-32 relative">
            
            <header class="sticky top-[80px] z-50 cyber-glass border-b border-tech px-4 py-3 shadow-sm">
                <div class="container mx-auto flex items-center gap-4">
                    <button @click="goBack" class="w-10 h-10 flex items-center justify-center rounded-full bg-surface border border-tech transition-colors hover-neon-red">
                        <ArrowLeft class="w-5 h-5 text-primary" />
                    </button>
                    <h1 class="font-black text-xl uppercase tracking-widest text-foreground">{{ zone?.name }}</h1>
                </div>
            </header>

            <div class="sticky top-[144px] z-40 w-full bg-background border-b border-tech pt-3 overflow-x-auto no-scrollbar shadow-sm scroll-smooth">
                <div class="flex px-4 gap-2">
                    <button v-for="parent in groupedCategories" :key="parent.id"
                            :id="`tab-${parent.id}`"
                            @click="scrollToCategory(parent.id)"
                            class="px-5 py-2.5 rounded-t-[14px] text-xs font-black uppercase transition-all duration-300 relative whitespace-nowrap"
                            :class="activeTab === parent.id 
                                ? 'bg-surface text-f1-red border-t border-x border-f1-red/50 shadow-[0_-4px_15px_-3px_rgba(225,6,0,0.2)] z-10' 
                                : 'bg-surface/50 text-muted border-t border-x border-transparent hover:text-primary mt-1 opacity-70 hover:opacity-100'">
                        {{ parent.name }}
                    </button>
                </div>
            </div>

            <div class="container mx-auto py-6 px-4 space-y-12 relative z-0">
                <div v-for="parent in groupedCategories" :key="parent.id" 
                     :id="`category-block-${parent.id}`"
                     :data-category-id="parent.id"
                     class="category-section pt-4 animate-fade-in"> 
                    
                    <h2 class="text-xl font-black uppercase mb-6 text-foreground tracking-widest border-l-4 border-f1-red pl-3">
                        {{ parent.name }}
                    </h2>
                    
                    <div v-for="sub in parent.subcategories" :key="sub.id" :id="`subcategory-${sub.id}`" class="mb-10">
                        
                        <h3 class="text-xs font-mono font-bold text-muted uppercase tracking-widest mb-4 flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-primary/50"></span> {{ sub.name }}
                        </h3>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div v-for="product in sub.products" :key="product.id" 
                                 @click="openBottomSheet(product)"
                                 class="bg-surface rounded-[20px] border border-tech p-3 relative flex flex-col group transition-all duration-500 overflow-hidden shadow-sm cursor-pointer hover-neon-red"
                                 :class="product.available_stock <= 0 ? 'opacity-60 grayscale' : ''">
                                
                                <div v-if="product.available_stock <= 0" class="absolute inset-0 z-30 cyber-glass flex items-center justify-center rounded-[20px]">
                                    <span class="bg-foreground text-background text-[9px] font-black uppercase tracking-widest px-3 py-1.5 rounded-md shadow-xl">Agotado</span>
                                </div>

                                <div class="absolute inset-x-0 top-0 h-[140px] overflow-hidden rounded-t-[20px] pointer-events-none z-0">
                                    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[150%] h-[150%] bg-gradient-to-tr from-f1-red/15 via-background to-telemetry-green/10 opacity-70 blur-[30px] group-hover:scale-125 transition-transform duration-700 ease-[cubic-bezier(0.16,1,0.3,1)]"></div>
                                </div>

                                <div class="relative z-10 w-full h-32 flex items-center justify-center mb-4 mt-2">
                                    <img :src="getImageUrl(product.image_url)" class="w-full h-full object-contain drop-shadow-[0_15px_15px_rgba(0,0,0,0.3)] transition-transform duration-500 group-hover:scale-[1.15] pointer-events-none">
                                </div>
                                
                                <div class="relative z-20 flex flex-col flex-1 justify-end">
                                    <h4 class="text-[11px] font-sans font-black uppercase text-foreground leading-tight line-clamp-2 mb-2">
                                        {{ product.name }}
                                    </h4>
                                    <div class="flex items-end justify-between mt-auto">
                                        <div class="text-sm font-mono font-black text-telemetry-green">
                                            <span class="text-[9px] text-muted tracking-widest block leading-none mb-0.5">Desde</span>
                                            <span class="text-[9px] text-muted mr-0.5 tracking-widest">BS.</span>{{ product.min_price }}
                                        </div>
                                        <div class="w-6 h-6 rounded-full bg-background border border-tech flex items-center justify-center text-primary group-hover:bg-f1-red group-hover:text-white group-hover:border-f1-red transition-colors shrink-0">
                                            <Plus :size="14" strokeWidth="3" />
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
            <div v-if="isBottomSheetOpen" class="fixed inset-x-0 bottom-0 z-[70] cyber-glass border-t border-tech rounded-t-[32px] pt-6 pb-8 shadow-[0_-10px_40px_rgba(0,0,0,0.5)] flex flex-col max-h-[95vh]">
                
                <div class="w-12 h-1.5 bg-border rounded-full mx-auto mb-4 shrink-0"></div>
                
                <div class="px-6 relative mb-4">
                    <button @click="closeBottomSheet" class="absolute left-6 top-1/2 -translate-y-1/2 text-muted hover:text-f1-red transition-colors"><X :size="24" /></button>
                    <h3 class="text-sm font-mono font-black text-center uppercase text-muted tracking-widest truncate px-10">{{ selectedProduct?.name }}</h3>
                </div>

                <div class="relative w-full mb-6">
                    
                    <button v-show="currentSkuIndex > 0" @click="prevSku" class="absolute left-2 top-1/2 -translate-y-1/2 z-20 w-8 h-8 rounded-full bg-surface border border-tech flex items-center justify-center text-primary shadow-lg transition-transform active:scale-90">
                        <ChevronLeft :size="18" strokeWidth="3" />
                    </button>
                    <button v-show="selectedProduct && currentSkuIndex < selectedProduct.skus.length - 1" @click="nextSku" class="absolute right-2 top-1/2 -translate-y-1/2 z-20 w-8 h-8 rounded-full bg-surface border border-tech flex items-center justify-center text-primary shadow-lg transition-transform active:scale-90">
                        <ChevronRight :size="18" strokeWidth="3" />
                    </button>

                    <div ref="carouselRef" @scroll="onCarouselScroll" class="flex overflow-x-auto snap-x snap-mandatory no-scrollbar w-full px-[10vw] gap-[2.5vw] py-4">
                        
                        <div v-for="(sku, index) in selectedProduct?.skus" :key="sku.id"
                             class="w-[80vw] shrink-0 snap-center rounded-[24px] bg-surface border border-tech flex flex-col items-center p-6 relative shadow-md transition-all duration-500 overflow-hidden spread-card-anim"
                             :style="{ '--delay': `${index * 0.1}s` }"
                             :class="sku.available_stock <= 0 ? 'opacity-50 grayscale' : (currentSkuIndex === index ? 'border-f1-red shadow-neon-red scale-100' : 'scale-[0.95] opacity-80')">
                            
                            <div v-if="sku.available_stock <= 0" class="absolute top-4 left-4 z-20 bg-foreground text-background text-[10px] font-black uppercase tracking-widest px-2 py-1 rounded shadow-md">Agotado</div>
                            <div v-if="currentSkuIndex === index && sku.available_stock > 0" class="absolute top-4 right-4 z-20 bg-telemetry-green text-black text-[10px] font-black uppercase tracking-widest px-2 py-1 rounded shadow-md">Activo</div>

                            <div class="absolute inset-0 bg-gradient-to-tr from-f1-red/10 via-transparent to-telemetry-green/10 blur-[30px] z-0 pointer-events-none"></div>
                            
                            <img :src="getImageUrl(sku.image_url)" class="relative z-10 w-48 h-48 object-contain drop-shadow-[0_20px_25px_rgba(0,0,0,0.3)] mb-6 transition-transform duration-500">
                            
                            <h4 class="relative z-10 text-xl font-sans font-black uppercase text-foreground leading-tight text-center mb-2">{{ sku.name }}</h4>
                            
                            <div class="relative z-10 text-center text-3xl font-mono font-black text-telemetry-green">
                                <span class="text-sm text-muted mr-1 tracking-widest">BS.</span>{{ sku.price }}
                            </div>
                        </div>

                    </div>
                </div>

                <div class="px-6">
                    <div class="flex items-center gap-4 mb-4 bg-background border border-tech rounded-[16px] p-2">
                        <button @click="decreaseQty" class="w-12 h-12 rounded-xl bg-surface flex items-center justify-center text-primary hover:text-f1-red transition-colors" :disabled="quantity <= 1 || selectedSku?.available_stock <= 0"><Minus :size="20" strokeWidth="3" /></button>
                        
                        <div class="flex-1 flex flex-col items-center justify-center">
                            <span class="font-mono font-black text-2xl text-foreground leading-none" :class="selectedSku?.available_stock <= 0 ? 'text-muted' : ''">{{ selectedSku?.available_stock > 0 ? quantity : 0 }}</span>
                            <span v-if="selectedSku?.available_stock <= 5 && selectedSku?.available_stock > 0" class="text-[9px] font-mono text-f1-red uppercase tracking-widest mt-1">
                                Quedan {{ selectedSku.available_stock }}
                            </span>
                        </div>
                        
                        <button @click="increaseQty" class="w-12 h-12 rounded-xl bg-surface flex items-center justify-center text-primary hover:text-telemetry-green transition-colors" :disabled="!selectedSku || quantity >= selectedSku.available_stock || selectedSku.available_stock <= 0"><Plus :size="20" strokeWidth="3" /></button>
                    </div>
                    
                    <button @click="addToCart" 
                            class="w-full py-4 font-sans font-black uppercase tracking-widest rounded-[16px] flex items-center justify-center gap-3 transition-all duration-300"
                            :class="selectedSku?.available_stock > 0 
                                ? 'bg-foreground text-background shadow-[0_4px_20px_rgba(255,255,255,0.2)] hover:scale-[0.98]' 
                                : 'bg-surface text-muted border border-tech cursor-not-allowed'">
                        <ShoppingCart v-if="selectedSku?.available_stock > 0" :size="20" />
                        <PackageX v-else :size="20" />
                        {{ selectedSku?.available_stock > 0 ? 'Agregar al Pedido' : 'Sin Existencias' }}
                    </button>
                </div>

            </div>
        </Transition>

    </ShopLayout>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

/* Transiciones del Bottom Sheet */
.slide-up-enter-active, .slide-up-leave-active {
    transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.4s ease;
}
.slide-up-enter-from, .slide-up-leave-to {
    transform: translateY(100%);
    opacity: 0;
}

.fade-enter-active, .fade-leave-active {
    transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
}

/* Animación de entrada inicial para la grilla (Fade in suave) */
.animate-fade-in {
    animation: fadeIn 0.4s ease-out forwards;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Animación tipo Baraja para los SKUs al abrir el Bottom Sheet */
.spread-card-anim {
    animation: spreadOut 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
    animation-delay: var(--delay);
}
@keyframes spreadOut {
    0% { opacity: 0; transform: scale(0.8) translateY(20px); }
    100% { opacity: 1; transform: scale(1) translateY(0); }
}
</style>