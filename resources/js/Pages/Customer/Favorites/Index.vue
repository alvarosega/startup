<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, router, usePage, Link } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import SkuCard from '@/Components/Customer/Product/SkuCard.vue';
import { PackageSearch, Heart, Loader2, ChevronLeft, ChevronRight, Zap } from 'lucide-vue-next';

const props = defineProps({
    favoriteProducts: Array,
    activeSkus: Array,
    selectedId: [String, Number]
});

const page = usePage();
const scrollContainer = ref(null);
const isAuth = computed(() => !!page.props.auth?.customer);
const isChanging = ref(false);

// --- 1. HIDRATACIÓN DE INVITADOS (Blindaje z-index 0) ---
onMounted(() => {
    if (!isAuth.value && !page.url.includes('ids')) {
        const localFavs = JSON.parse(localStorage.getItem('guest_favorites') || '[]');
        if (localFavs.length > 0) {
            const ids = localFavs.map(f => f.product_id);
            router.get(route('customer.favorites.index'), { ids }, { 
                preserveState: true, 
                replace: true 
            });
        }
    }
});

// --- 2. NAVEGACIÓN DE HARDWARE ---
const scroll = (direction) => {
    if (!scrollContainer.value) return;
    scrollContainer.value.scrollBy({ left: direction === 'left' ? -300 : 300, behavior: 'smooth' });
};

const selectProduct = (id) => {
    if (String(id) === String(props.selectedId) || isChanging.value) return;
    
    isChanging.value = true;
    router.get(route('customer.favorites.index'), { 
        active_id: id,
        ids: !isAuth.value ? props.favoriteProducts.map(p => p.id) : undefined
    }, {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => isChanging.value = false
    });
};
</script>

<template>
    <ShopLayout>
        <Head :title="`Archivo de Selección | ${page.props.auth?.customer?.name || 'Invitado'}`" />

        <div class="w-full min-h-screen bg-transparent pb-32 relative z-10">
            
            <div class="sticky top-[72px] lg:top-[80px] z-40 glass-titanium border-b border-white/5 pb-4 pt-0 transition-all duration-500">
                <div class="max-w-7xl mx-auto px-6 lg:px-8">
                    
                    <div class="header-standard pt-6 mb-8">
                        <div class="title-block-wrapper">
                            <Heart :size="16" class="text-black dark:text-white fill-current animate-pulse" />
                            <h1 class="text-black dark:text-white">Archivo de Selección</h1>
                        </div>
                    </div>

                    <div v-if="favoriteProducts.length > 0" class="relative group/carousel overflow-visible">
                        <button @click="scroll('left')" 
                                class="hidden lg:flex absolute -left-4 top-10 z-50 w-10 h-10 items-center justify-center rounded-full bg-background/40 backdrop-blur-xl border border-border/50 text-foreground opacity-0 group-hover/carousel:opacity-100 transition-all hover:scale-110 active:scale-90 shadow-xl">
                            <ChevronLeft :size="20" stroke-width="3" />
                        </button>
                        <button @click="scroll('right')" 
                                class="hidden lg:flex absolute -right-4 top-10 z-50 w-10 h-10 items-center justify-center rounded-full bg-background/40 backdrop-blur-xl border border-border/50 text-foreground opacity-0 group-hover/carousel:opacity-100 transition-all hover:scale-110 active:scale-90 shadow-xl">
                            <ChevronRight :size="20" stroke-width="3" />
                        </button>

                        <div ref="scrollContainer" class="flex overflow-x-auto snap-x snap-center no-scrollbar gap-8 md:gap-10 pb-4 pt-2 scroll-smooth">
                            <button v-for="product in favoriteProducts" :key="product.id"
                                    @click="selectProduct(product.id)"
                                    class="group/item relative flex flex-col items-center snap-center shrink-0 cursor-pointer w-[80px] md:w-[100px] transition-all duration-500 ease-ios outline-none active:scale-90"
                                    :class="{ 'is-active': String(selectedId) === String(product.id) }">
                                
                                <div class="relative w-20 h-20 md:w-24 md:h-24 flex items-center justify-center mb-4">
                                    
                                    <div class="absolute inset-0 transition-all duration-700 pointer-events-none layer-gpu opacity-20 md:group-hover/item:opacity-60 scale-[1.1] md:group-hover/item:scale-[1.5] blur-[25px]"
                                         style="background: radial-gradient(circle, #ef4444 10%, transparent 70%)">
                                    </div>

                                    <div v-if="String(selectedId) === String(product.id)" 
                                         class="absolute inset-0 rounded-full p-[3px] prismatic-ring animate-in zoom-in-75 duration-500 shadow-f1-glow">
                                        <div class="w-full h-full rounded-full bg-background/80 backdrop-blur-sm"></div>
                                    </div>

                                    <img :src="product.image" 
                                         class="relative z-10 w-16 h-16 md:w-20 md:h-20 object-contain transition-all duration-500 group-hover/item:scale-110 group-hover/item:-translate-y-3 layer-gpu drop-shadow-hardware"
                                         :alt="product.name">
                                </div>

                                <span class="text-[9px] md:text-[10px] font-black tracking-[0.1em] uppercase text-center leading-tight line-clamp-2 w-full transition-all duration-300"
                                      :class="[
                                          String(selectedId) === String(product.id) 
                                          ? 'text-primary' 
                                          : 'text-foreground/60 group-hover/item:text-foreground'
                                      ]">
                                    {{ product.name }}
                                </span>
                            </button>
                            <div class="w-20 shrink-0 invisible"></div> 
                        </div>
                    </div>

                    <div v-else class="py-12 glass-chassis-skeleton animate-pulse rounded-3xl flex flex-col items-center text-center">
                        <Heart :size="24" class="text-primary/20 mb-2" />
                        <p class="text-[10px] uppercase font-black text-primary/40 italic">No hay productos en el radar</p>
                    </div>
                </div>
            </div>

            <main class="max-w-7xl mx-auto px-6 lg:px-8 mt-12 relative">
                
                <Transition name="fade">
                    <div v-if="isChanging" class="absolute inset-0 z-50 glass-titanium flex items-center justify-center rounded-3xl">
                        <Loader2 class="w-10 h-10 text-primary animate-spin" />
                    </div>
                </Transition>

                <div v-if="activeSkus.length > 0" class="section-reveal">
                    <div class="header-standard mb-10 pb-4 border-b border-white/5">
                        <div class="title-block-wrapper">
                            <Zap :size="16" class="text-black dark:text-primary fill-current" />
                            <h2 class="text-xl md:text-2xl text-black dark:text-white">Variantes Disponibles</h2>
                        </div>
                        <span class="text-[10px] font-mono text-foreground/40 uppercase whitespace-nowrap">
                            [ Existencias: {{ activeSkus.length }} ]
                        </span>
                    </div>
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4 md:gap-6">
                        <SkuCard v-for="sku in activeSkus" :key="sku.id" :sku="sku" />
                    </div>
                </div>

                <div v-else-if="favoriteProducts.length > 0" class="flex flex-col items-center justify-center py-32 text-center opacity-40">
                    <PackageSearch :size="64" stroke-width="1" class="text-foreground mb-6" />
                    <p class="text-[10px] font-black uppercase tracking-[0.3em] text-foreground italic max-w-sm">Selecciona un producto superior para desplegar sus variantes en el archivo</p>
                </div>
            </main>
        </div>
    </ShopLayout>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

.layer-gpu { 
    will-change: transform, opacity, filter; 
    transform: translateZ(0); 
}

.ease-ios { 
    transition-timing-function: cubic-bezier(0.32, 0.72, 0, 1); 
}

/* ENCABEZADO PRISMÁTICO ESTÁNDAR */
.header-standard { display: flex; align-items: flex-end; gap: 1rem; }
.title-block-wrapper { position: relative; display: flex; align-items: center; gap: 0.85rem; flex-grow: 1; padding-bottom: 8px; }
.title-block-wrapper h2 { font-size: 11px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.4em; }
.title-block-wrapper h1 { font-size: 11px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.4em; }

.title-block-wrapper::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 1px;
    background: linear-gradient(to right, #ff0000, #ff7f00, #ffff00, #00ff00, #0000ff, #4b0082, #8b00ff);
    opacity: 0.8;
}

/* ANILLO PRISMÁTICO ANIMADO */
.prismatic-ring {
    background: linear-gradient(45deg, #ff0000, #ff7f00, #ffff00, #00ff00, #0000ff, #4b0082, #8b00ff);
    background-size: 200% 200%;
    animation: prismatic-flow 3s linear infinite;
}

@keyframes prismatic-flow {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

/* GLASS TITANIUM Y SKELETON */
.glass-titanium {
    background-color: hsl(var(--background) / 0.6);
    backdrop-filter: blur(20px) saturate(180%);
}

.dark .glass-titanium {
    background-color: hsl(var(--background) / 0.8);
}

.glass-chassis-skeleton {
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(40px);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

/* FÍSICA DE MATERIALES */
.drop-shadow-hardware {
    filter: drop-shadow(0 10px 12px rgba(0,0,0,0.25));
}

.dark .drop-shadow-hardware {
    filter: drop-shadow(0 15px 20px rgba(0,0,0,0.6));
}

.shadow-f1-glow {
    box-shadow: 0 0 15px -3px hsl(var(--primary));
}

.fade-enter-active, .fade-leave-active { transition: opacity 0.5s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.section-reveal {
    animation: reveal 0.8s cubic-bezier(0.32, 0.72, 0, 1) both;
}

@keyframes reveal {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>